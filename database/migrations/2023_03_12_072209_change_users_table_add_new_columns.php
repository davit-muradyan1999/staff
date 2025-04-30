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
            $table->date('birthday')->nullable()->after('company_phone');
            $table->foreignId('citizenship_id')->after('company_phone')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('gender_id')->after('company_phone')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('registration_address')->nullable()->after('company_phone');
            $table->string('address_of_residence')->nullable()->after('company_phone');
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
            $table->dropForeign(['citizenship_id']);
            $table->dropForeign(['gender_id']);
            $table->dropColumn([
                'birthday',
                'citizenship_id',
                'gender_id',
                'registration_address',
                'address_of_residence',
            ]);
        });
    }
};
