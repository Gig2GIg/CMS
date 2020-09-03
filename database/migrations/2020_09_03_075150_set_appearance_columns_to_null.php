<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetAppearanceColumnsToNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_aparences', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_aparences` CHANGE `height` `height` DOUBLE(7,2) NULL, CHANGE `weight` `weight` DOUBLE(7,2) NULL, CHANGE `hair` `hair` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `eyes` `eyes` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `race` `race` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_aparences', function (Blueprint $table) {
            //
        });
    }
}
