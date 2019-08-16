<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->date('date');
            $table->time('time');
            $table->text('location');
            $table->text('description');
            $table->string('url');
            $table->text('personal_information');
            $table->string('phone');
            $table->string('email');
            $table->text('other_info');
            $table->string('union');
            $table->string('contract');
            $table->string('production');
            $table->boolean('status');
            $table->integer('user_id')->unsigned();
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
        Schema::dropIfExists('auditions');
    }
}
