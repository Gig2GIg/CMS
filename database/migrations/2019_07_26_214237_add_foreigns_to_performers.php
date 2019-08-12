<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignsToPerformers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('performers', function (Blueprint $table) {
            $table->foreign('performer_id') ->references('id')
                ->on('users')
                ->onDelete('cascade');
              $table->foreign('director_id') ->references('id')
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
        Schema::table('performers', function (Blueprint $table) {
            //

            $table->dropForeign('performers_director_id_foreign');
            $table->dropForeign('performers_performer_id_foreign');
        });
    }
}
