<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('term_of_use');
            $table->string('privacy_policy');
            $table->string('app_info');
            $table->string('contact_us');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_settings');
    }
}
