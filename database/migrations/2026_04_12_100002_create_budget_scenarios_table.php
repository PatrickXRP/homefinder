<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('total_budget_eur');
            $table->integer('purchase_budget_eur');
            $table->integer('renovation_budget_eur')->default(0);
            $table->integer('annual_costs_max_eur')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_scenarios');
    }
};
