<?php

namespace App\Http\Requests\Api\V1\Module;

use App\Rules\Api\ColumnInSameBoard;
use App\Rules\Api\MaxColumnsPerBoard;
use App\Services\Boards\ModuleService;
use Illuminate\Foundation\Http\FormRequest;

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
    public function withValidator($validator)
    {
        $data = $validator->getData();
        $newColumns = 0;

        foreach ($data as $key => $value) {
            if (array_key_exists($key, ModuleService::$availableColumns) && $value == 0) {
                $newColumns++;
            }
        }

        if ($newColumns != 0) {
            $validator->addRules(['board' => [new MaxColumnsPerBoard($this->route('board'), $newColumns)]]);
        }
    }
}
