<?php

namespace App\Rules\Team;

use Illuminate\Contracts\Validation\Rule;

class NotMemberOfTeam implements Rule
{
    protected $team;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($team)
    {
        $this->team = $team;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !$this->team->members()->where('email', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.is_member_of_the_team');
    }
}
