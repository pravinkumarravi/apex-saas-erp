<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('audit_number', 50);
            $table->string('title');
            $table->enum('audit_type', ['internal', 'supplier', 'regulatory', 'customer'])->default('internal');
            $table->string('auditor_name');
            $table->decimal('score_percentage', 5, 2)->default(0);
            $table->enum('status', ['scheduled', 'in_progress', 'passed', 'capa_required'])->default('scheduled');
            $table->text('findings')->nullable();
            $table->date('scheduled_date');
            $table->timestamps();

            $table->unique(['tenant_id', 'audit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_audits');
    }
};
