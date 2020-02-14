<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFeedbackTableColumnsToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `feedbacks` CHANGE `evaluator_id` `evaluator_id` INT(10) UNSIGNED NULL, CHANGE `evaluation` `evaluation` INT(10) UNSIGNED NULL, CHANGE `slot_id` `slot_id` INT(10) UNSIGNED NULL, CHANGE `callback` `callback` TINYINT(1) NULL, CHANGE `work` `work` ENUM('vocals','acting','dancing') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `comment` `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
