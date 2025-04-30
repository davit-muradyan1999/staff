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
        Schema::table('worker_info', function (Blueprint $table) {
            $table->string('migration_card_file_2')->nullable()->after('passport_translation_file_4');
            $table->string('migration_card_file_1')->nullable()->after('passport_translation_file_4');
            $table->string('migration_account_file_2')->nullable()->after('passport_translation_file_4');
            $table->string('migration_account_file_1')->nullable()->after('passport_translation_file_4');
            $table->string('temporary_registration_file_2')->nullable()->after('passport_translation_file_4'); // Временная регистрация по месту пребывания
            $table->string('temporary_registration_file_1')->nullable()->after('passport_translation_file_4');
            $table->string('rvp_file_2')->nullable()->after('passport_translation_file_4');
            $table->string('rvp_file_1')->nullable()->after('passport_translation_file_4');
            $table->string('vnj_file_2')->nullable()->after('passport_translation_file_4');
            $table->string('vnj_file_1')->nullable()->after('passport_translation_file_4');

            $table->string('payment_check_file_3')->nullable()->after('passport_translation_file_4');
            $table->string('payment_check_file_2')->nullable()->after('passport_translation_file_4');
            $table->string('payment_check_file_1')->nullable()->after('passport_translation_file_4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('worker_info', function (Blueprint $table) {
            $table->dropColumn([
                'migration_card_file_2',
                'migration_card_file_1',
                'migration_account_file_2',
                'migration_account_file_1',
                'temporary_registration_file_2',
                'temporary_registration_file_1',
                'rvp_file_2',
                'rvp_file_1',
                'vnj_file_2',
                'vnj_file_1',
                'payment_check_file_3',
                'payment_check_file_2',
                'payment_check_file_1',
            ]);
        });
    }
};
