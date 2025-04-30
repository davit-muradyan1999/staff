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
        Schema::create('user_login_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['WEB', 'MOBILE'])->default('WEB');
            $table->enum('social_type', ['YANDEX', 'MAILRU'])->nullable();
            $table->string('user_agent');
            $table->string('remote_addr');
            $table->string('username');
            $table->unsignedBigInteger('user_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_login_histories');
    }
};
