<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('job_title');
            $table->text('job_url'); // Keep as text for long URLs
            $table->string('job_url_hash', 64); // SHA-256 hash for unique constraint
            $table->string('company')->nullable();
            $table->string('location')->nullable();
            $table->string('source')->nullable(); // Indeed, Kalibrr, etc.
            $table->integer('match_score')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Prevent duplicate bookmarks using hash
            $table->unique(['user_id', 'job_url_hash']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_bookmarks');
    }
};

