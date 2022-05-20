<?php

namespace App\Http\Requests\Api\V1\Card;

use App\Rules\Api\CardInSameColumn;
use Illuminate\Foundation\Http\FormRequest;

class OrderCardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $card = $this->route('card');
        $column = $card->relationLoaded('column')
            ? $card->column
            : $card->column()->withoutGlobalScopes()->first();

        return [
            'after' => [
                'present', 'integer', 'min:0',
                new CardInSameColumn($column),
            ],
        ];
    }
}
