<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysFinalCast extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('final_casts', function (Blueprint $table) {
            $table->foreign('performer_id') ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('audition_id') ->references('id')
                ->on('auditions')
                ->onDelete('cascade');
            $table->foreign('rol_id') ->references('id')
                ->on('roles')
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
        Schema::table('final_casts', function (Blueprint $table) {
            $table->dropForeign('final_casts_audition_id_foreign');
            $table->dropForeign('final_casts_performer_id_foreign');
            $table->dropForeign('final_casts_rol_id_foreign');
        });
    }
}
