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
        Schema::create('employee_balance_lists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['SHIFT', 'BONUS', 'TRANSFER'])->default('SHIFT');
            $table->enum('action', ['PLUS', 'MINUS'])->default('PLUS');
            $table->foreignId('company_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('job_check_list_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->float('amount', 13, 2);
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
        Schema::dropIfExists('employee_balance_lists');
    }
};
