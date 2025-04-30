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
        Schema::create('job_check_list_shift_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_check_list_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('shift_reason_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('job_check_list_shift_reasons');
    }
};
