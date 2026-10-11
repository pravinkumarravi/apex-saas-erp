<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('module_slug', 100);
            $table->boolean('is_enabled')->default(true);
            $table->string('version', 20)->default('1.0.0');
            $table->json('settings')->nullable();
            $table->timestamp('installed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['tenant_id', 'module_slug']);
            $table->index(['tenant_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
