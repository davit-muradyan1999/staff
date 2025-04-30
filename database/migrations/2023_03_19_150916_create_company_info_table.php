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
        Schema::create('company_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('industry_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('quantity_employee_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('company_type_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('contact_city_id')->nullable()->constrained('cities')->onUpdate('cascade')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->year('foundation_date')->nullable();
            $table->json('who_are_we')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
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
        Schema::dropIfExists('company_info');
    }
};
