<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThumbUrlColumnInAuditionVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audition_videos', function (Blueprint $table) {
            DB::statement("ALTER TABLE  `audition_videos` CHANGE  `url`  `url` VARCHAR( 700 ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
            DB::statement("ALTER TABLE `audition_videos` ADD `thumbnail` VARCHAR( 700 ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `url`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audition_videos', function (Blueprint $table) {
            //
        });
    }
}
