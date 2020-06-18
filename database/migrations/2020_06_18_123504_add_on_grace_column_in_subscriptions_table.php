<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnGraceColumnInSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `subscriptions` ADD `grace_period` TINYINT NOT NULL DEFAULT '0' AFTER `updated_by`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
}
