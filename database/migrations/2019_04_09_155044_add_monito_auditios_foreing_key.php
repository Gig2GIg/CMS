<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonitoAuditiosForeingKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('monitors',function (Blueprint $table){
            $table->foreign('auditions_id') ->references('id')
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
        Schema::table('user_slots', function (Blueprint $table) {
            $table->dropForeign('monitors_auditons_id_foreign');

        });
    }
}
