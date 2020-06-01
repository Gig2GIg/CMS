<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeInAppPurchaseChangesInSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `subscriptions` CHANGE `stripe_id` `stripe_id` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `stripe_status` `stripe_status` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `stripe_plan` `stripe_plan` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `quantity` `quantity` INT(11) NULL");

            DB::statement("ALTER TABLE `subscriptions` ADD `purchase_platform` ENUM('web','android','ios') NOT NULL DEFAULT 'web' AFTER `ends_at`");

            DB::statement("ALTER TABLE `subscriptions` ADD `product_id` VARCHAR(255) NULL AFTER `stripe_plan`, ADD `original_transaction` VARCHAR(255) NULL AFTER `product_id`, ADD `current_transaction` VARCHAR(255) NULL AFTER `original_transaction`");

            DB::statement("ALTER TABLE `subscriptions` ADD `purchased_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `purchase_platform`");

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
