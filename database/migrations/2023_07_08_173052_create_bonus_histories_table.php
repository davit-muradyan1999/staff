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
        Schema::create('bonus_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['CONFIRMED', 'CANCELED'])->default('CONFIRMED');
            $table->foreignId('created_user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('job_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('bonus_histories');
    }
};
