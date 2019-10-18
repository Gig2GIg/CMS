<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->text('location')->nullable();
            $table->integer('slots')->unsigned();
            $table->enum('type',['time','number']);
            $table->string('length');
            $table->string('start');
            $table->string('end');
            $table->integer('round')->unsigned();
            $table->boolean('status');
            $table->integer('auditions_id')->unsigned();
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
        Schema::dropIfExists('appointments');
    }
}
