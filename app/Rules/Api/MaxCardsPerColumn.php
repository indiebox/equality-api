<?php

namespace App\Rules\Api;

use App\Models\Column;
use Illuminate\Contracts\Validation\ImplicitRule;

class MaxCardsPerColumn implements ImplicitRule
{
    /** Max cards per column.*/
    public const MAX_CARDS = 100;

    protected $column;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Column $column)
    {
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
        return $this->column->cards()->count() < static::MAX_CARDS;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.max_cards_per_column', ['max' => static::MAX_CARDS]);
    }
}
