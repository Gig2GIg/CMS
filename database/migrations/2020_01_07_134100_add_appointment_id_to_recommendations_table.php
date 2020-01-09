<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppointmentIdToRecommendationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->integer('appointment_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropColumn('appointment_id');
        });
    }
}
