<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThumbUrlColumnInAuditionVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audition_videos', function (Blueprint $table) {
            $table->string('url', 700)->change();
            $table->string('thumbnail', 700)->after('url')->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audition_videos', function (Blueprint $table) {
            //
        });
    }
}
