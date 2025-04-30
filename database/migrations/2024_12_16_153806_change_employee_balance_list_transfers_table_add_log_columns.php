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
        Schema::table('employee_balance_list_transfers', function (Blueprint $table) {
            $table->boolean('is_csrp_success')->after('response_log')->delfault(0); // create-salary-registry-payment - Оплатить реестр
            $table->datetime('csrp_at')->after('response_log')->nullable(); // create-salary-registry-payment - Оплатить реестр
            $table->text('csrp_response_log')->after('response_log')->nullable(); // create-salary-registry-payment - Оплатить реестр
            $table->text('csrp_request_log')->after('response_log')->nullable(); // create-salary-registry-payment - Оплатить реестр

            $table->text('sprsr_response_log')->after('response_log')->nullable(); // salary-payment-registry-submit-result - Получить результат подписания платежного реестра сотрудников
            $table->datetime('sprsr_at')->after('response_log')->nullable(); // salary-payment-registry-submit-result - Получить результат подписания платежного реестра сотрудников

            $table->boolean('is_sprs_signed')->after('response_log')->delfault(0); // salary-payment-registry-submit - Подписать платежный реестр сотрудников
            $table->datetime('sprs_at')->after('response_log')->nullable(); // salary-payment-registry-submit - Подписать платежный реестр сотрудников
            $table->text('sprs_response_log')->after('response_log')->nullable(); // salary-payment-registry-submit - Подписать платежный реестр сотрудников
            $table->text('sprs_request_log')->after('response_log')->nullable(); // salary-payment-registry-submit - Подписать платежный реестр сотрудников

            $table->string('sgprcr_status')->after('response_log')->nullable(); //salary-get-payment-registry-create-result - Получить результат создания черновика платежного реестра
            $table->datetime('sgprcr_at')->after('response_log')->nullable(); //salary-get-payment-registry-create-result - Получить результат создания черновика платежного реестра
            $table->text('sgprcr_response_log')->after('response_log')->nullable(); //salary-get-payment-registry-create-result - Получить результат создания черновика платежного реестра
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_balance_list_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'csrp_response_log', 'csrp_request_log', 'csrp_at', 'is_csrp_success', 'sprsr_response_log',
                'sprsr_at', 'sprs_response_log', 'sprs_request_log', 'sprs_at', 'is_sprs_signed',
                'sgprcr_status', 'sgprcr_at', 'sgprcr_response_log',
            ]);
        });
    }
};
