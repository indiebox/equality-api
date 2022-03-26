<?php

use Illuminate\Database\Migrations\Migration;

class ChangeColumnOrderInTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `columns` CHANGE `board_id` `board_id` BIGINT(20) UNSIGNED NOT NULL AFTER `id`");
        DB::statement("ALTER TABLE `boards` CHANGE `project_id` `project_id` BIGINT(20) UNSIGNED NOT NULL AFTER `id`");
    }
}
