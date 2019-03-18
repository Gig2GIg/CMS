<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeingKeyAuditionContribuitors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audition_contributors',function (Blueprint $table){
            $table->foreign('audition_id')
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
       Schema::table('audition_contributors', function (Blueprint $table) {
           $table->dropForeign('audition_contributors_audition_id_foreign');
       });
    }
}
