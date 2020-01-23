<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NullAllFieldsToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {

            DB::statement('ALTER TABLE `appointments` CHANGE `slots` `slots` INT(10) UNSIGNED NULL');

            DB::statement("ALTER TABLE `appointments` CHANGE `type` `type` ENUM('time','number') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");

            DB::statement('ALTER TABLE `appointments` CHANGE `length` `length` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `appointments` CHANGE `start` `start` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `appointments` CHANGE `end` `end` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `appointments` CHANGE `round` `round` INT(10) UNSIGNED NULL');

            DB::statement('ALTER TABLE `appointments` CHANGE `status` `status` TINYINT(1) NULL');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            //
        });
    }
}
