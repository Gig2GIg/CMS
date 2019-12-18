<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToNotificationHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('notification_history', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('notification_history', function (Blueprint $table) {
          
            $table->enum('status', ['read', 'unread','aceppted', 'rejected'])->default('unread');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_history', function (Blueprint $table) {
            //
        });
    }
}
