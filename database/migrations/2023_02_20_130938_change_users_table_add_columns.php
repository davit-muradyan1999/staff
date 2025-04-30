<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ADMIN', 'WORKER', 'COMPANY', 'MODERATOR', 'ADMINISTRATOR'])->after('id');
            $table->string('company_phone')->nullable()->after('email_verified_at');
            $table->string('company_name')->nullable()->after('email_verified_at');
            $table->string('phone')->nullable()->after('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'company_phone',
                'company_name',
                'phone',
            ]);
        });
    }
};
