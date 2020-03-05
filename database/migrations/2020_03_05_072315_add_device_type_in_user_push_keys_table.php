<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeviceTypeInUserPushKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_push_keys', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_push_keys` ADD `device_type` ENUM('android','ios','web') NULL DEFAULT NULL AFTER `device_token`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_push_keys', function (Blueprint $table) {
            //
        });
    }
}
