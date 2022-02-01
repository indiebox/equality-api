<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\Http;

class ValidRecaptcha implements ImplicitRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value == null || !is_string($value)) {
            return false;
        }

        $response = Http::asForm()->post(config('recaptcha.url'), [
            'secret' => config('recaptcha.private_key'),
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        return $response['success'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.recaptcha');
    }
}
