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
            $table->foreign('auditions_id')
                ->references('id')
                ->on('auditions')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
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
       Schema::table('audition_contributors', function (Blueprint $table) {
           $table->dropForeign('audition_contributors_user_id_foreign');
           $table->dropForeign('audition_contributors_auditions_id_foreign');
       });
    }
}
