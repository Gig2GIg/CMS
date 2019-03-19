<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeingKeyAuditionDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auditions_dates', function (Blueprint $table) {
            $table->foreign('auditions_id')
                ->references('id')
                ->on('auditions')
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
        Schema::table('auditions_dates', function (Blueprint $table) {
            $table->dropForeign('auditions_dates_auditions_id_foreign');
        });
    }
}
