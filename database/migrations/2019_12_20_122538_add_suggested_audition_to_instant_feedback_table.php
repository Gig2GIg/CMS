<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuggestedAuditionToInstantFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instant_feedback', function (Blueprint $table) {
                $table->integer('suggested_appointment_id')->unsigned();
                // $table->unique(['appointment_id']);
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instant_feedback', function (Blueprint $table) {
            //
        });
    }
}
