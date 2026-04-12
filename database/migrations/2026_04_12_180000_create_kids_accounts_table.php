<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kids_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pin', 4);
            $table->string('emoji')->default('👤');
            $table->string('color', 7)->default('#3b82f6');
            $table->integer('age')->nullable();
            $table->boolean('is_active')->default(true);

            // Modules
            $table->boolean('module_photo_swiper')->default(true);
            $table->boolean('module_property_swiper')->default(false);
            $table->boolean('module_property_overview')->default(false);

            // Filters
            $table->json('allowed_country_ids')->nullable(); // null = all
            $table->json('allowed_regions')->nullable(); // null = all
            $table->integer('filter_price_min')->nullable();
            $table->integer('filter_price_max')->nullable();
            $table->integer('filter_bedrooms_min')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kids_accounts');
    }
};
