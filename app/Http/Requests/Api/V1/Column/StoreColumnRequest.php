<?php

namespace App\Http\Requests\Api\V1\Column;

use App\Rules\Api\ColumnInSameBoard;
use App\Rules\Api\MaxColumnsPerBoard;
use Illuminate\Foundation\Http\FormRequest;

class StoreColumnRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'after_column' => ['nullable', 'integer', 'min:0', new ColumnInSameBoard($this->route('board'))],

            'board' => [new MaxColumnsPerBoard($this->route('board'))],
        ];
    }
}
