<?php

namespace App\Rules\Api;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class ColumnInSameBoard implements Rule
{
    protected $request;

    protected $board;

    protected $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request, Board $board)
    {
        $this->request = $request;
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
        if ($value == 0) {
            return true;
        }

        $this->attribute = $attribute;

        $column = Column::where('board_id', $this->board->id)->find((int)$value);

        if ($column == null) {
            return false;
        }

        $this->request->merge([$attribute => $column]);

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
