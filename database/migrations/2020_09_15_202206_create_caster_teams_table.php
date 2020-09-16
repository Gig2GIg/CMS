<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCasterTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caster_teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('admin_id')->unsigned();
            $table->integer('member_id')->unsigned();
            $table->boolean('is_selected')->nullable()->default(NULL)->comment('To determine which team is selected in case caster is in multiple teams');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caster_teams');
    }
}
