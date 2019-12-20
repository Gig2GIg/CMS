<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssignNoToUserAuditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_auditions', function (Blueprint $table) {
            $table->integer('assign_no')->nullable()->after('group_no');
            $table->integer('assign_no_by')->nullable()->after('assign_no');
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
