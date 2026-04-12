<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_plan_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_plan_id')->constrained('route_plans')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->time('visit_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_plan_properties');
    }
};
