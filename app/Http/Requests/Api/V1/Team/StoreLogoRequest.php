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
            'logo' => ['nullable', 'image', 'dimensions:ratio=1', 'mimes:jpeg,jpg,png', 'max:4096'],
        ];
    }
}