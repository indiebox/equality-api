<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
            'password' => ['required', 'string', 'confirmed', Password::default()],
        ];
    }
}
