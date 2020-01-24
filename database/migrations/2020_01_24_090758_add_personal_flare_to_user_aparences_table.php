<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonalFlareToUserAparencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_aparences', function (Blueprint $table) {
            $table->string('personal_flare')->nullable()->after('race');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_aparences', function (Blueprint $table) {
            $table->dropColumn('personal_flare');
        });
    }
}
