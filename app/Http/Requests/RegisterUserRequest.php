<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    protected const MAX_ATTEMPTS = 1;

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
            'password' => ['required', 'confirmed', Password::default()],
        ];
    }

    /**
     * Ensure the request is not rate limited and hit attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            RateLimiter::hit($this->throttleKey());

            return;
        }

        event(new Lockout($this));

        throw new ThrottleRequestsException(trans('errors.throttle'));
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    protected function throttleKey()
    {
        return $this->ip();
    }
}
