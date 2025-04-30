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
        Schema::create('employee_balance_list_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_balance_list_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'SENT', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->string('payment_registry_id')->nullable();
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
        Schema::dropIfExists('employee_balance_list_transfers');
    }
};
