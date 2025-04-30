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
        Schema::create('company_info_advantages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_info_id')->nullable()->constrained('company_info')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('advantage_employee_id')->nullable()->constrained('advantage_employees')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_info_advantages');
    }
};
