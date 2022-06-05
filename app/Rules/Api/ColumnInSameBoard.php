<?php

namespace App\Rules\Api;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class ColumnInSameBoard implements Rule, ValidatorAwareRule
{
    protected $attribute;

    protected $column;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        protected Request $request,
        protected Board $board
    ) {
    }

    public function setValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->column == null) {
                return;
            }

            $data = [$this->attribute => $this->column];
            $validator->setData(array_merge($validator->getData(), $data));
            $this->request->merge($data);
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

        $column = Column::where('board_id', $this->board->id)->find((int)$value);

        if ($column == null) {
            return false;
        }

        $this->column = $column;

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
