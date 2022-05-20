<?php

namespace App\Rules\Api;

use App\Models\Card;
use App\Models\Column;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

/**
 * None: This rule setup actual \App\Models\Card after complete validation.
 * If you need to get the original data(id), get it from model.
 */
class CardInSameColumn implements Rule, ValidatorAwareRule
{
    protected $column;

    protected $attribute;

    protected $card;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function setValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->card == null) {
                return;
            }

            $data = [$this->attribute => $this->card];
            $validator->setData(array_merge($validator->getData(), $data));
            request()->merge($data);
        });
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value == 0) {
            return true;
        }

        $this->attribute = $attribute;

        $card = Card::where('column_id', $this->column->id)->find((int)$value);

        if ($card == null) {
            return false;
        }

        $this->card = $card;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.exists', ['attribute' => $this->attribute]);
    }
}
