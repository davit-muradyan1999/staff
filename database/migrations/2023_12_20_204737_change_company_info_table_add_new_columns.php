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
        Schema::table('company_info', function (Blueprint $table) {
            $table->json('location')->nullable()->after('who_are_we');
            $table->string('tax_declarations_last_period')->nullable()->after('who_are_we');
            $table->string('tax_declarations_last_year')->nullable()->after('who_are_we');
            $table->string('certificate_of_no_debt')->nullable()->after('who_are_we');
            $table->string('passport_gen_director')->nullable()->after('who_are_we');
            $table->string('extract_egrul')->nullable()->after('who_are_we');
            $table->string('tax_certificate')->nullable()->after('who_are_we');
            $table->string('charter')->nullable()->after('who_are_we');
            $table->json('bank_bip')->nullable()->after('who_are_we');
            $table->json('correspondent_account')->nullable()->after('who_are_we');
            $table->json('checking_account')->nullable()->after('who_are_we');
            $table->json('bank_name')->nullable()->after('who_are_we');
            $table->json('okfs')->nullable()->after('who_are_we');
            $table->json('okopf')->nullable()->after('who_are_we');
            $table->json('okved')->nullable()->after('who_are_we');
            $table->json('okpo')->nullable()->after('who_are_we');
            $table->json('address')->nullable()->after('who_are_we');
            $table->json('kpp')->nullable()->after('who_are_we');
            $table->json('ogrn')->nullable()->after('who_are_we');
            $table->json('inn')->nullable()->after('who_are_we');
            $table->string('organization_card')->nullable()->after('who_are_we');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'tax_declarations_last_period',
                'tax_declarations_last_year',
                'certificate_of_no_debt',
                'passport_gen_director',
                'extract_egrul',
                'tax_certificate',
                'charter',
                'bank_bip',
                'correspondent_account',
                'checking_account',
                'bank_name',
                'okfs',
                'okopf',
                'okved',
                'okpo',
                'address',
                'kpp',
                'ogrn',
                'inn',
                'organization_card',
            ]);
        });
    }
};
