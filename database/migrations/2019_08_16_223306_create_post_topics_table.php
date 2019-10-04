<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_topics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('post_id')->unsigned();
            $table->foreign('post_id')
            ->references('id')
            ->on('posts')
            ->onDelete('cascade');

            $table->bigInteger('topic_id')->unsigned();
            $table->foreign('topic_id')
            ->references('id')
            ->on('topics')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_topics');
    }
}
