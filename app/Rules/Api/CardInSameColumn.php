<?php

namespace App\Rules\Api;

use App\Models\Card;
use App\Models\Column;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class CardInSameColumn implements Rule
{
    protected $request;

    protected $column;

    protected $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request, Column $column)
    {
        $this->request = $request;
        $this->column = $column;
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
        $this->attribute = $attribute;

        $card = Card::where('column_id', $this->column->id)->find((int)$value);

        if ($card == null) {
            return false;
        }

        $this->request->merge([$attribute => $card]);

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
