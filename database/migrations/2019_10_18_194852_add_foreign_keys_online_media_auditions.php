<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysOnlineMediaAuditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('online_media_auditions', function (Blueprint $table) {
            $table->foreign('appointment_id') ->references('id')
                ->on('appointments')
                ->onDelete('cascade');
            $table->foreign('performer_id') ->references('id')
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
        Schema::table('online_media_auditions', function (Blueprint $table) {
            $table->dropForeign('online_media_auditions_appointment_id_foreign');
            $table->dropForeign('online_media_auditions_performer_id_foreign');

        });
    }
}
