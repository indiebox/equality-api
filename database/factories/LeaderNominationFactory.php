<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LeaderNominationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_at' => now(),
        ];
    }

    /** Setup nominated user.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $user
     * @return static
     */
    public function nominated($user) {
        return $this->for($user, 'nominated');
    }

    /** Setup voter.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $user
     * @return static
     */
    public function voter($user) {
        return $this->for($user, 'voter');
    }

    /** Setup project for the nomination.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $project
     * @return static
     */
    public function project($project) {
        return $this->for($project);
    }
}
