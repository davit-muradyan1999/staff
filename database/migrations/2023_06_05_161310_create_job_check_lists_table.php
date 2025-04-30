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
        Schema::create('job_check_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('job_time_graphic_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['CONFIRMED', 'CANCELED'])->default('CONFIRMED');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
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
        Schema::dropIfExists('job_check_lists');
    }
};
