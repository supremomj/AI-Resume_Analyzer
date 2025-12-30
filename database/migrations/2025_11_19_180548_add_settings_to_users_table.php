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
            // Job Preferences
            $table->boolean('email_notifications')->default(true)->after('recommended_field');
            $table->string('alert_frequency', 10)->default('daily')->after('email_notifications'); // daily, weekly, never
            $table->json('preferred_job_types')->nullable()->after('alert_frequency'); // ['Full-time', 'Part-time', 'Contract', 'Remote']
            
            // Privacy & Security
            $table->boolean('profile_public')->default(false)->after('preferred_job_types');
            $table->boolean('show_contact')->default(false)->after('profile_public');
            
            // Account Preferences
            $table->string('language', 5)->default('en')->after('show_contact'); // en, tl
            $table->integer('jobs_per_page')->default(20)->after('language'); // 10, 20, 30, 50
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications',
                'alert_frequency',
                'preferred_job_types',
                'profile_public',
                'show_contact',
                'language',
                'jobs_per_page',
            ]);
        });
    }
};
