<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('slots_id')->unsigned()->nullable();
            $table->integer('auditions_id')->unsigned();
            $table->boolean('favorite')->default(false);
            $table->integer('roles_id')->unsigned()->nullable();
            $table->enum('status',['reserved', 'checked']);
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
        Schema::dropIfExists('user_slots');
    }
}
