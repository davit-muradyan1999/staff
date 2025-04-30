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
        Schema::table('job_check_lists', function (Blueprint $table) {
            $table->integer('commission')->after('job_time_graphic_id')->nullable();
            $table->integer('tax')->after('job_time_graphic_id')->nullable();
            $table->integer('salary')->after('job_time_graphic_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_check_lists', function (Blueprint $table) {
            $table->dropColumn([
                'commission',
                'tax',
                'salary',
            ]);
        });
    }
};
