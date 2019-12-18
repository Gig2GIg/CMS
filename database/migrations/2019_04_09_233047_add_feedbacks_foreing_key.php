<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeedbacksForeingKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->foreign('appointment_id') ->references('id')
                ->on('appointments')
                ->onDelete('cascade');
            $table->foreign('user_id') ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('evaluator_id') ->references('id')
                ->on('users')
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
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropForeign('feedbacks_appointment_id_foreign');
            $table->dropForeign('feedbacks_user_id_foreign');
            $table->dropForeign('feedbacks_evaluator_id_foreign');
        });
    }
}
