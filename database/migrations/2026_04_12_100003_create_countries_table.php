<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_local')->nullable();
            $table->string('code', 2);
            $table->string('flag_emoji')->nullable();
            $table->string('continent')->nullable();
            $table->string('status')->default('onderzoek');
            $table->integer('match_score')->default(0);
            $table->text('match_summary')->nullable();
            $table->jsonb('match_details')->nullable();
            $table->boolean('foreigners_can_buy')->default(true);
            $table->text('foreigners_notes')->nullable();
            $table->boolean('eu_member')->default(false);
            $table->integer('avg_price_per_m2_eur')->nullable();
            $table->decimal('purchase_costs_pct', 5, 2)->nullable();
            $table->decimal('annual_property_tax_pct', 5, 2)->nullable();
            $table->text('annual_costs_notes')->nullable();
            $table->integer('realistic_budget_min_eur')->nullable();
            $table->text('realistic_budget_notes')->nullable();
            $table->string('internet_quality')->nullable();
            $table->string('healthcare_quality')->nullable();
            $table->string('language_barrier')->nullable();
            $table->string('expat_community')->nullable();
            $table->boolean('international_schools')->default(false);
            $table->decimal('flight_hours_from_dubai', 4, 1)->nullable();
            $table->string('nearest_airport')->nullable();
            $table->text('flight_connections_notes')->nullable();
            $table->longText('ai_report')->nullable();
            $table->timestamp('ai_report_generated_at')->nullable();
            $table->jsonb('pros')->nullable();
            $table->jsonb('cons')->nullable();
            $table->jsonb('sources')->nullable();
            $table->timestamp('researched_at')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
