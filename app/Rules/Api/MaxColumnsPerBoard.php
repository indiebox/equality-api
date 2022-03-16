<?php

namespace App\Rules\Api;

use App\Models\Board;
use Illuminate\Contracts\Validation\ImplicitRule;

class MaxColumnsPerBoard implements ImplicitRule
{
    /** Max columns per board.*/
    public const MAX_COLUMNS = 50;

    protected $board;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Board $board)
    {
        $this->board = $board;
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
        return $this->board->columns()->count() < static::MAX_COLUMNS;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.max_columns_per_board', ['max' => static::MAX_COLUMNS]);
    }
}
