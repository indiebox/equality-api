<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InviteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    /** Indicate that the invite should be accepted.
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function accepted()
    {
        return $this->state(function (array $attributes) {
            return [
                'accepted_at' => now(),
            ];
        });
    }

    /** Indicate that the invite should be declined.
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function declined()
    {
        return $this->state(function (array $attributes) {
            return [
                'declined_at' => now(),
            ];
        });
    }

    /** Setup inviter user to the invite.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $user
     * @return static
     */
    public function inviter($user)
    {
        return $this->for($user, 'inviter');
    }

    /** Setup invited user to the invite.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $user
     * @return static
     */
    public function invited($user)
    {
        return $this->for($user, 'invited');
    }

    /** Setup team to the invite.
     * @param \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model $user
     * @return static
     */
    public function team($team)
    {
        return $this->for($team);
    }
}
