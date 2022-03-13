<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Database\Seeder;

class ColumnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $boards = Board::all();

        foreach ($boards as $board) {
            $board->columns()->saveMany(Column::factory(3)->make());
        }
    }
}
