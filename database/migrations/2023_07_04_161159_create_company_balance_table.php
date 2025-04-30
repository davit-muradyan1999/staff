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
        Schema::create('company_balance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->float('time', 13, 2)->nullable();
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
        Schema::dropIfExists('company_balance');
    }
};
