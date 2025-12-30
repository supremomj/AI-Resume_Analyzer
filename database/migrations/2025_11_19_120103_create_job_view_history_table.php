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
        Schema::create('job_view_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('job_title');
            $table->text('job_url');
            $table->string('job_url_hash', 64); // SHA-256 hash for indexing
            $table->string('company')->nullable();
            $table->string('location')->nullable();
            $table->string('source')->nullable();
            $table->integer('match_score')->nullable();
            $table->text('description')->nullable();
            $table->integer('view_count')->default(1);
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['user_id', 'viewed_at']);
            $table->index('job_url_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_view_history');
    }
};
