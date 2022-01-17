<?php

namespace App\Http\Requests\Api\V1\Team;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $user = User::where('email', $this->email)->first();

            if ($user == null) {
                $validator->errors()->add('email', trans('validation.invalid_user'));

                return;
            }

            if ($this->isMemberOfTeam($user)) {
                $validator->errors()->add('email', trans('validation.is_member_of_team'));

                return;
            }

            if ($this->isAlreadyInvited($user)) {
                $validator->errors()->add('email', trans('validation.already_invited'));

                return;
            }

            $this->merge(['invited' => $user]);
        });
    }

    protected function isMemberOfTeam($user)
    {
        $team = $this->route('team');
        $isMemberOfTeam = $team->members()
            ->where('user_id', $user->id)
            ->exists();

        return $isMemberOfTeam;
    }

    protected function isAlreadyInvited($user)
    {
        $team = $this->route('team');
        $inviteExists = Invite::where('team_id', $team->id)
            ->where('invited_id', $user->id)
            ->exists();

        return $inviteExists;
    }
}
