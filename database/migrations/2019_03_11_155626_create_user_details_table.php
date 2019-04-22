<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('city');
            $table->integer('state');
            $table->date('birth');
            $table->enum('subscription',[1,2,3])->default(1);
            $table->string('profesion');
            $table->string('stage_name')->default("n/a");
            $table->string('agency_name')->default("n/a");
            $table->enum('type',[1,2,3]);
            $table->integer('user_id')->unsigned();
            $table->json('location');
            $table->string('zip',5);
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
        Schema::dropIfExists('user_details');
    }
}
