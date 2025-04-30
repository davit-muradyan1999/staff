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
        Schema::create('establishment_statuses', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['DRAFT', 'SENDED', 'CONFIRMED', 'PASSIVATED', 'REJECTED'])->default('DRAFT');
            $table->foreignId('establishment_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('created_user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('updated_user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('establishment_statuses');
    }
};
