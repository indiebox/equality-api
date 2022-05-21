<?php

namespace App\Http\Requests\Api\V1\Module;

use App\Rules\Api\ColumnInSameBoard;
use App\Rules\Api\MaxColumnsPerBoard;
use App\Services\Modules\KanbanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $sameBoardRule = new ColumnInSameBoard($this, $board);

        return [
            'todo_column_id' => [
                'required', 'integer', 'min:0',
                Rule::when($this->todo_column_id != 0, $this->getDifferentRule('todo_column_id')),
                $sameBoardRule,
            ],
            'inprogress_column_id' => [
                'required', 'integer', 'min:0',
                Rule::when($this->inprogress_column_id != 0, $this->getDifferentRule('inprogress_column_id')),
                $sameBoardRule,
            ],
            'done_column_id' => [
                'required', 'integer', 'min:0',
                Rule::when($this->done_column_id != 0, $this->getDifferentRule('done_column_id')),
                $sameBoardRule,
            ],

            // Optional columns.
            'onreview_column_id' => [
                'sometimes', 'required', 'integer', 'min:0',
                Rule::when($this->onreview_column_id != 0, $this->getDifferentRule('onreview_column_id')),
                $sameBoardRule,
            ],
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
            if (array_key_exists($key, KanbanService::$availableColumns) && $value == 0) {
                $newColumns++;
            }
        }

        if ($newColumns != 0) {
            $validator->addRules(['board' => [new MaxColumnsPerBoard($this->route('board'), $newColumns)]]);
        }
    }

    protected function getDifferentRule($column)
    {
        return array_map(function ($value) {
                return 'different:' . $value;
        }, array_keys(array_diff_key(KanbanService::$availableColumns, [$column => 0])));
    }
}
