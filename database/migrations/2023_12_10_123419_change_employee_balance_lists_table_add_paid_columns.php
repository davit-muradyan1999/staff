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
        Schema::table('employee_balance_lists', function (Blueprint $table) {
            $table->boolean('is_paid')->after('amount')->default(0);
            $table->dateTime('paid_created_at')->nullable()->after('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_balance_lists', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'paid_created_at']);
        });
    }
};
