<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserAuditionsForeingKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_auditions',function (Blueprint $table){
            $table->foreign('user_id') ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('user_auditions',function (Blueprint $table){
            $table->foreign('auditions_id') ->references('id')
                ->on('auditions')
                ->onDelete('cascade');
        });

        Schema::table('user_auditions',function (Blueprint $table){
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
        Schema::table('user_auditions', function (Blueprint $table) {
//            $table->dropForeign('user_auditions_user_id_foreign');
//            $table->dropForeign('user_auditions_auditions_id_foreign');
//            $table->dropForeign('user_auditions_roles_id_foreign');

        });
    }
}
