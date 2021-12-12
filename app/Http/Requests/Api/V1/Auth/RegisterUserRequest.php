<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'between:2,50'],
            'email' => ['required', 'email', 'max:128', 'unique:users'],
            // TODO: remove 'confirmed' rule?
            'password' => ['required', 'string', 'confirmed', 'between:6,32'],
        ];
    }
}
