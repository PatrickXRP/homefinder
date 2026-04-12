<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('type');
            $table->string('subject');
            $table->longText('body');
            $table->string('language')->nullable();
            $table->string('tone')->default('neutraal');
            $table->string('status')->default('concept');
            $table->timestamp('sent_at')->nullable();
            $table->string('gmail_thread_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_emails');
    }
};
