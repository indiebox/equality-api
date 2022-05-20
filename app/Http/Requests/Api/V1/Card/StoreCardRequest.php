<?php

namespace App\Http\Requests\Api\V1\Card;

use App\Rules\Api\CardInSameColumn;
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
            'after_card' => ['nullable', 'integer', 'min:0', new CardInSameColumn($this->route('column'))],

            'column' => [new MaxCardsPerColumn($this->route('column'))],
        ];
    }
}
