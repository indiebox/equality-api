<?php

namespace App\Http\Requests\Api\V1\Card;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'after' => ['present', 'nullable', 'integer'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('after')) {
                $card = Card::where('column_id', $this->route('card')->column->id)->find($this->after);

                if ($card == null) {
                    $validator->errors()->add('after', trans('validation.exists', ['attribute' => 'after']));

                    return;
                }

                $this->merge(['after' => $card]);
            }
        });
    }
}
