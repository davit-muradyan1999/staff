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
        Schema::create('worker_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('citizenship_id')->nullable()->constrained('citizenships')->onUpdate('cascade')->onDelete('cascade');
            $table->json('first_name')->nullable();
            $table->json('last_name')->nullable();
            $table->json('surname')->nullable();
            $table->date('birthday')->nullable();
            $table->json('place_of_birth')->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('genders')->onUpdate('cascade')->onDelete('cascade');
            $table->json('series_and_number')->nullable();
            $table->json('issued_by')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->date('validity')->nullable();
            $table->json('residence_address')->nullable();

            $table->json('insurance_certificate')->nullable();
            $table->string('insurance_certificate_file')->nullable();

            $table->string('passport_file_1')->nullable();
            $table->string('passport_file_2')->nullable();
            $table->string('passport_file_3')->nullable();
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
        Schema::dropIfExists('worker_info');
    }
};
