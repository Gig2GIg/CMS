<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionColumnsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE `users` ADD `is_premium` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_active`, ADD `stripe_plan_id` VARCHAR(255) NULL AFTER `is_premium`, ADD `stripe_plan_name` VARCHAR(255) NULL AFTER `stripe_plan_id`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
