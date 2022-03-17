<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->sentences(3, true),
            'order' => 1,
        ];
    }

    public function order($order)
    {
        return $this->state([
            'order' => $order,
        ]);
    }

    /** Setup column for the card.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $column
     * @return static
     */
    public function column($column)
    {
        return $this->for($column);
    }
}
