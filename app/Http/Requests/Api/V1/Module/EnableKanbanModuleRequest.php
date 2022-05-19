<?php

namespace App\Http\Requests\Api\V1\Module;

use App\Rules\Api\ColumnInSameBoard;
use Illuminate\Foundation\Http\FormRequest;

// use Illuminate\Validation\Validator;

class EnableKanbanModuleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $board = $this->route('board');

        return [
            'todo_column_id' => ['required', 'integer', 'min:0', new ColumnInSameBoard($this, $board)],
            'inprogress_column_id' => ['required', 'integer', 'min:0', new ColumnInSameBoard($this, $board)],
            'done_column_id' => ['required', 'integer', 'min:0', new ColumnInSameBoard($this, $board)],

            // Optional columns.
            'onreview_column_id' => ['sometimes', 'required', 'integer', 'min:0', new ColumnInSameBoard($this, $board)],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    // public function withValidator($validator)
    // {
    //     $validator->after(function (Validator $validator) {
    //         $newColumns = $this->safe()->collect()->where(fn($value) => $value == 0)->count();
    //         if ($newColumns != 0) {
    //             $validator->add
    //         }

    // 'board' => [new MaxColumnsPerBoard($this->route('board'))],
    //     });
    // }
}
