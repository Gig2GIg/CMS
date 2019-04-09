<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationSettingUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_setting_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('status', ['on', 'off']);
            $table->string('code');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id') ->references('id')
            ->on('users')
            ->onDelete('cascade');
            
            $table->integer('notification_setting_id')->unsigned();
            $table->foreign('notification_setting_id') ->references('id')
            ->on('notification_settings')
            ->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_setting_user');
    }
}
