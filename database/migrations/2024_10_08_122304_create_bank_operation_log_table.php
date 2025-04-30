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
        Schema::create('bank_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['SUCCESS', 'ERROR']);
            $table->enum('type', ['ADD_RECIPIENT', 'SALARY_CREATE_EMPLOYEE', 'SALARY_PAYMENT_REGISTRY', 'SALARY_PAYMENT_REGISTRY_RESULT']);
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('sended_correlation_id')->nullable();
            $table->string('received_correlation_id')->nullable();
            $table->text('request_log')->nullable();
            $table->text('response_log')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_operation_log');
    }
};
