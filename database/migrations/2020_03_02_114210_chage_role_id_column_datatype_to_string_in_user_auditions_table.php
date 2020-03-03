<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChageRoleIdColumnDatatypeToStringInUserAuditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_auditions', function (Blueprint $table) {
            // $table->string('rol_id', 255)->nullable()->change();
            DB::statement("ALTER TABLE `user_auditions` CHANGE `rol_id` `rol_id` VARCHAR( 255 ) NULL DEFAULT NULL");      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_auditions', function (Blueprint $table) {
            //
        });
    }
}
