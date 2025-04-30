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
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['DRAFT', 'SENDED', 'CONFIRMED', 'PASSIVATED'])->default('DRAFT');
            $table->enum('gender', ['BOTH', 'MALE', 'FEMALE'])->default('BOTH');
            $table->json('name');
            $table->json('obligation');
            $table->json('requirement');
            $table->boolean('absence_disability')->default(0);
            $table->integer('salary')->nullable();
            $table->integer('tax')->nullable();
            $table->integer('commission')->nullable();
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
        Schema::dropIfExists('establishments');
    }
};
