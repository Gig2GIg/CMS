<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('auditions_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('evaluator_id')->unsigned();
            $table->integer('evaluation')->unsigned();
            $table->integer('slot_id')->unsigned();
            $table->boolean('callback');
            $table->enum('work', ['vocals', 'acting', 'dancing']);
            $table->boolean('favorite')->default(false);
            $table->unique(['auditions_id','user_id','evaluator_id']);
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
        Schema::dropIfExists('feedbacks');
    }
}
