<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeForignKeyTypeInRecommendationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recommendations', function (Blueprint $table) {
            DB::statement("ALTER TABLE `recommendations` DROP FOREIGN KEY `recommendations_marketplace_id_foreign`"); 

            DB::statement("ALTER TABLE `recommendations` ADD CONSTRAINT `recommendations_marketplace_id_foreign` FOREIGN KEY (`marketplace_id`) REFERENCES `marketplaces`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recommendations', function (Blueprint $table) {
            //
        });
    }
}
