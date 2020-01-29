<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NullAllFieldsToAuditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auditions', function (Blueprint $table) {
            DB::statement('ALTER TABLE `auditions` CHANGE `personal_information` `personal_information` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `auditions` CHANGE `union` `union` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `auditions` CHANGE `contract` `contract` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `auditions` CHANGE `production` `production` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `auditions` CHANGE `status` `status` TINYINT(1) NOT NULL DEFAULT 0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auditions', function (Blueprint $table) {
            //
        });
    }
}
