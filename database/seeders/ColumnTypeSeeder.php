<?php

namespace Database\Seeders;

use App\Models\ColumnType;
use Illuminate\Database\Seeder;

class ColumnTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ColumnType::firstOrCreate(
            ['id' => ColumnType::NONE],
            ['name' => 'none']
        );
        ColumnType::firstOrCreate(
            ['id' => ColumnType::TODO],
            ['name' => 'todo']
        );
        ColumnType::firstOrCreate(
            ['id' => ColumnType::IN_PROGRESS],
            ['name' => 'in_progress']
        );
        ColumnType::firstOrCreate(
            ['id' => ColumnType::DONE],
            ['name' => 'done']
        );
        ColumnType::firstOrCreate(
            ['id' => ColumnType::ON_REVIEW],
            ['name' => 'on_review']
        );
    }
}
