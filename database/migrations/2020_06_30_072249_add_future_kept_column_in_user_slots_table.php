<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFutureKeptColumnInUserSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_slots', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_slots` ADD `future_kept` TINYINT(1) NULL DEFAULT '0' AFTER `favorite`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_slots', function (Blueprint $table) {
            //
        });
    }
}
