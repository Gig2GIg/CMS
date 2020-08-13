<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGenderDescColumnInToUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_details` CHANGE `gender` `gender` ENUM('agender','female','gender diverse','gender expansive','gender fluid','genderqueer','intersex','male','non-binary','transfemale/transfeminine','transmale/transmasculine','two-spirit','Prefer not to answer','self describe') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL");

            DB::statement("ALTER TABLE `user_details` ADD `gender_desc` VARCHAR(255) NULL AFTER `gender`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            //
        });
    }
}
