<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('columns', function (Blueprint $table) {
            $table->unsignedTinyInteger('column_type_id')->default(0)->after('board_id');
            $table->foreign(['column_type_id'])->references('id')->on('column_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('columns', function (Blueprint $table) {
            $table->dropForeign(['column_type_id']);
            $table->dropColumn('column_type_id');
        });
    }
};
