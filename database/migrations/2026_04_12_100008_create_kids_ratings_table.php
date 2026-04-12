<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kids_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('kid_name');
            $table->string('kid_emoji')->nullable();
            $table->string('rating');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kids_ratings');
    }
};
