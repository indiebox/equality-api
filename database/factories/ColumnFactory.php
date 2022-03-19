<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ColumnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'order' => 1,
        ];
    }

    public function order($order)
    {
        return $this->state([
            'order' => $order,
        ]);
    }

    /** Setup board for the column.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $board
     * @return static
     */
    public function board($board)
    {
        return $this->for($board);
    }
}
