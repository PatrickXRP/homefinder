<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('kid_name');
            $table->string('kid_pin', 4);
            $table->integer('photo_index')->default(0);
            $table->string('image_url');
            $table->string('rating'); // super_tof, leuk, gaat_wel, niet_leuk, bah
            $table->timestamps();

            $table->index(['kid_name', 'image_url']);
            $table->index(['property_id', 'kid_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_swipes');
    }
};
