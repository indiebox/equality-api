<?php

namespace App\Http\Requests\Api\V1\Card;

use App\Rules\Api\MaxCardsPerColumn;
use Illuminate\Foundation\Http\FormRequest;

class MoveCardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'column' => [new MaxCardsPerColumn($this->route('column'))],
        ];
    }
}
