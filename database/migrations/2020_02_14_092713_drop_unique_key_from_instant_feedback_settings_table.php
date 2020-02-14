<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUniqueKeyFromInstantFeedbackSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instant_feedback_settings', function (Blueprint $table) {
            $table->dropUnique('instant_feedback_settings_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instant_feedback_settings', function (Blueprint $table) {
            $table->unique('instant_feedback_settings_user_id_unique');
        });
    }
}
