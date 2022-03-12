<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(2, false),
        ];
    }

    public function deleted()
    {
        return $this->state([
            'deleted_at' => now(),
        ]);
    }

    /** Setup project for the board.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $project
     * @return static
     */
    public function project($project)
    {
        return $this->for($project);
    }
}
