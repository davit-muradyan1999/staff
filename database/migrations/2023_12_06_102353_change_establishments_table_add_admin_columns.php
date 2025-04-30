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
        Schema::table('establishments', function (Blueprint $table) {
            $table->enum('admin_status', ['DRAFT', 'SENDED', 'CONFIRMED', 'PASSIVATED', 'REJECTED'])->nullable()->after('status');
            $table->foreignId('admin_user_id')->nullable()->after('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('admin_status_updated_at')->nullable()->after('img');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropForeign(['admin_user_id']);
            $table->dropColumn([
                'admin_status',
                'admin_user_id',
                'admin_status_updated_at',
            ]);
        });
    }
};
