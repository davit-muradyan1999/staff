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
        Schema::create('employee_balance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->float('time', 13, 2);
            $table->float('balance', 13, 2);
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
        Schema::dropIfExists('employee_balance');
    }
};
