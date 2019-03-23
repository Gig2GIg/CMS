<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeingKeyMarketplaceCategoryToMarketplaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplaces', function (Blueprint $table) {
            $table->foreign('marketplace_category_id')
                    ->references('id')
                    ->on('marketplace_categories')
                    ->onDelete('cascade');
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
            // $table->dropForeign('marketplaces_marketplaces_categories_id_foreign');
        });
    }
}
