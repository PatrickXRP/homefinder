<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('property_sources')->nullOnDelete();
            $table->string('name');
            $table->string('external_id')->nullable();
            $table->text('url')->nullable();
            $table->string('status')->default('gezien_online');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->bigInteger('asking_price')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->bigInteger('asking_price_eur')->nullable();
            $table->integer('price_per_m2')->nullable();
            $table->integer('year_built')->nullable();
            $table->integer('living_area_m2')->nullable();
            $table->integer('plot_area_m2')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->string('energy_class')->nullable();
            $table->string('condition')->nullable();
            $table->string('water_type')->nullable();
            $table->string('water_name')->nullable();
            $table->boolean('has_sauna')->default(false);
            $table->boolean('has_jetty')->default(false);
            $table->boolean('has_guest_house')->default(false);
            $table->boolean('year_round_accessible')->default(true);
            $table->boolean('own_road')->default(false);
            $table->integer('my_score')->nullable();
            $table->integer('ai_score')->nullable();
            $table->text('ai_analysis')->nullable();
            $table->date('listed_date')->nullable();
            $table->integer('days_on_market')->nullable();
            $table->date('viewing_date')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('checklist')->nullable();
            $table->jsonb('images')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->jsonb('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
