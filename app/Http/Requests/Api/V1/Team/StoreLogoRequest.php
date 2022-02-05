<?php

namespace App\Http\Requests\Api\V1\Team;

use Illuminate\Foundation\Http\FormRequest;

class StoreLogoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'logo' => ['required', 'image', 'mimes:jpeg,jpg,png', 'dimensions:ratio=1', 'max:4096'],
        ];
    }
}
