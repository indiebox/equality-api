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
        return [
            'after' => ['present', 'nullable', 'integer', new CardInSameColumn($this->route('card')->column)],
        ];
    }
}
