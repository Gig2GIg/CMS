<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserAditionsSlotsForeingKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_slots',function (Blueprint $table){
            $table->foreign('user_id') ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('user_slots',function (Blueprint $table){
            $table->foreign('appointment_id') ->references('id')
                ->on('appointments')
                ->onDelete('cascade');
        });

        Schema::table('user_slots',function (Blueprint $table){
            $table->foreign('slots_id') ->references('id')
                ->on('slots')
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
            $table->dropForeign('user_slots_user_id_foreign');
            $table->dropForeign('user_slots_appointment_id_foreign');
            $table->dropForeign('user_slots_slots_id_foreign');


        });
    }
}
