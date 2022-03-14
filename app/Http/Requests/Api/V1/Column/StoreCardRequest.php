<?php

namespace App\Http\Requests\Api\V1\Column;

use App\Rules\Api\MaxCardsPerColumn;
use Illuminate\Foundation\Http\FormRequest;

class StoreCardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'column' => [new MaxCardsPerColumn($this->route('column'))],
        ];
    }
}