<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNullableEmailMarketplaceCategoryIdInMarketplacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplaces', function (Blueprint $table) {
            DB::statement("ALTER TABLE `marketplaces` CHANGE `email` `email` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
            DB::statement("ALTER TABLE `marketplaces` CHANGE `marketplace_category_id` `marketplace_category_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL;");
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
