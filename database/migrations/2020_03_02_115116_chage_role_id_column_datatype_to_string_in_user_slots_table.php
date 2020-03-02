<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChageRoleIdColumnDatatypeToStringInUserSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_slots', function (Blueprint $table) {
            DB::statement("ALTER TABLE `user_slots` CHANGE `roles_id` `roles_id` VARCHAR( 255 ) NULL DEFAULT NULL");      
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
            //
        });
    }
}
