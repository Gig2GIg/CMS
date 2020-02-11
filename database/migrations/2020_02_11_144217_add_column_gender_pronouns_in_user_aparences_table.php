<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnGenderPronounsInUserAparencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_aparences', function (Blueprint $table) {
            $table->string('gender_pronouns')->nullable()->after('personal_flare');
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
            $table->dropColumn('gender_pronouns');
        });
    }
}
