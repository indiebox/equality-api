<?php

namespace App\Rules\Team;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class NotInvitedInTeam implements Rule
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
        $userId = User::where('email', $value)->first()->id;

        return !$this->team->invites()->where('invited_id', $userId)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.already_invited');
    }
}
