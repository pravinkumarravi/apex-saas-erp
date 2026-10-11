<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills_of_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Finished Good
            $table->string('bom_number')->index();
            $table->string('version')->default('1.0');
            $table->decimal('quantity', 15, 4)->default(1.0000); // output quantity
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->text('routing_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'bom_number']);
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('bills_of_material')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Raw Material or Subassembly
            $table->decimal('quantity', 15, 4);
            $table->decimal('scrap_percentage', 5, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->index();
            $table->string('name');
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('capacity_per_day_hours', 5, 2)->default(8.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('bom_id')->constrained('bills_of_material')->cascadeOnDelete();
            $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete(); // for raw materials
            $table->foreignId('target_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete(); // for finished goods
            $table->decimal('target_quantity', 15, 4);
            $table->decimal('produced_quantity', 15, 4)->default(0);
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'qc_pending', 'completed', 'cancelled'])->default('planned');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'order_number']);
        });

        Schema::create('production_material_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('actual_consumed_quantity', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('inspection_number')->index();
            $table->string('inspectable_type'); // production_order, goods_receipt_note
            $table->unsignedBigInteger('inspectable_id');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('sample_size', 15, 4)->default(0);
            $table->decimal('passed_quantity', 15, 4)->default(0);
            $table->decimal('rejected_quantity', 15, 4)->default(0);
            $table->date('inspection_date');
            $table->enum('status', ['pass', 'fail', 'rework'])->default('pass');
            $table->json('checkpoints')->nullable(); // [{"parameter": "Dimensional tolerance", "result": "Pass"}]
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'inspection_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
        Schema::dropIfExists('production_material_consumptions');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('bills_of_material');
    }
};
