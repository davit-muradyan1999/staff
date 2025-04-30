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
            $table->foreignId('currency_id')->nullable()->after('passport_translation_file_4')->constrained()->onUpdate('cascade')->onDelete('cascade');

            $table->string('account_number')->nullable()->after('passport_translation_file_4');

            $table->json('recipient')->nullable()->after('passport_translation_file_4');
            $table->json('recipient_bank')->nullable()->after('passport_translation_file_4');

            $table->json('bik')->nullable()->after('passport_translation_file_4');

            $table->json('correspondent_account')->nullable()->after('passport_translation_file_4');

            $table->json('kpp')->nullable()->after('passport_translation_file_4');

            $table->string('swift_code')->nullable()->after('passport_translation_file_4');



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
            $table->dropForeign(['currency_id']);
            $table->dropColumn([
                'currency_id',
                'account_number',
                'recipient',
                'recipient_bank',
                'bik',
                'correspondent_account',
                'kpp',
                'swift_code',
            ]);
        });
    }
};
