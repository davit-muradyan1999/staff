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
        Schema::table('worker_info', function (Blueprint $table) {
            $table->string('current_migration_registration')->nullable()->after('passport_file_3');
            $table->string('migration_card')->nullable()->after('passport_file_3');

            $table->foreignId('disability_group_id')->nullable()->after('passport_file_3')->constrained('disability_groups')->onUpdate('cascade')->onDelete('cascade');
            $table->string('disability_group_file')->after('passport_file_3')->nullable();

            $table->json('medical_book')->nullable()->after('passport_file_3');
            $table->string('medical_book_file')->nullable()->after('passport_file_3');

            $table->json('snils')->nullable()->after('passport_file_3');
            $table->string('snils_file')->nullable()->after('passport_file_3');

            $table->json('inn')->nullable()->after('passport_file_3');
            $table->string('inn_file')->nullable()->after('passport_file_3');

            $table->string('passport_translation_file_4')->nullable()->after('passport_file_3');
            $table->string('passport_translation_file_3')->nullable()->after('passport_file_3');
            $table->string('passport_translation_file_2')->nullable()->after('passport_file_3');
            $table->string('passport_translation_file_1')->nullable()->after('passport_file_3');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('worker_info', function (Blueprint $table) {
            $table->dropForeign(['disability_group_id']);
            $table->dropColumn([
                'current_migration_registration',
                'migration_card',
                'disability_group_id',
                'disability_group_file',
                'medical_book',
                'medical_book_file',
                'snils',
                'snils_file',
                'inn',
                'inn_file',
                'passport_translation_file_4',
                'passport_translation_file_3',
                'passport_translation_file_2',
                'passport_translation_file_1',
            ]);
        });
    }
};
