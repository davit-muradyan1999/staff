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
        Schema::table('job_time_graphics', function (Blueprint $table) {
            $table->boolean('is_late_added')->after('lunch')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_time_graphics', function (Blueprint $table) {
            $table->dropColumn(['is_late_added']);
        });
    }
};
