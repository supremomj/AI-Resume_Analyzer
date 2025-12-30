<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('first_name')->after('id');
        $table->string('last_name')->after('first_name');
        $table->string('contact_number')->nullable()->after('last_name');
        $table->string('address')->nullable()->after('contact_number');
        $table->string('profile_photo')->nullable()->after('address');
    });
}
public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['first_name', 'last_name', 'contact_number', 'address', 'profile_photo']);
    });
}

};
