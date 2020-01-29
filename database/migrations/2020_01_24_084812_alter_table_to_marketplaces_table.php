<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableToMarketplacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplaces', function (Blueprint $table) {
            DB::statement('ALTER TABLE `marketplaces` CHANGE `phone_number` `phone_number` VARCHAR( 191 ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');

            DB::statement('ALTER TABLE `marketplaces` CHANGE `url_web` `url_web` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketplaces', function (Blueprint $table) {
            //
        });
    }
}
