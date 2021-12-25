<?php

namespace App\Http\Requests\Api\V1\Team;

use App\Rules\Team\NotInvitedInTeam;
use App\Rules\Team\NotMemberOfTeam;
use Illuminate\Foundation\Http\FormRequest;

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
            'email' => [
                'required', 'string', 'email', 'exists:users',
                new NotMemberOfTeam($this->route('team')), new NotInvitedInTeam($this->route('team')),
            ],
        ];
    }
}
