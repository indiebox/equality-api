<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
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
            'description' => $this->faker->sentence(),
        ];
    }

    /** Setup leader user to the project.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $user
     * @return static
     */
    public function leader($user) {
        return $this->for($user, 'leader');
    }

    /** Setup team to the project.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $team
     * @return static
     */
    public function team($team) {
        return $this->for($team);
    }
}
