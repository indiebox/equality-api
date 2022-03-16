<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
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
            $index = 0;

            foreach ($board->columns as $column) {
                if ($index == 0) {
                    $column->cards()->saveMany(Card::factory(3)->make());
                } else {
                    $column->cards()->save(Card::factory()->make());
                }

                $index++;
            }
        }
    }
}
