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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('provider')->nullable(); // Coursera, Udemy, edX, YouTube, etc.
            $table->text('url');
            $table->text('description')->nullable();
            $table->string('field'); // Maps to AI recommended_field (e.g. "Data Science", "Web Development")
            $table->boolean('is_free')->default(false);
            $table->decimal('rating', 3, 1)->nullable();
            $table->timestamps();

            $table->index('field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
