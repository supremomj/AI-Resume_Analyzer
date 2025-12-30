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
        Schema::table('users', function (Blueprint $table) {
            $table->json('ai_analysis')->nullable()->after('resume_path');
            $table->integer('resume_score')->nullable()->after('ai_analysis');
            $table->string('recommended_field')->nullable()->after('resume_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_analysis', 'resume_score', 'recommended_field']);
        });
    }
};
