<?php

namespace App\Http\Requests\Api\V1\Board;

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
            'board' => [new MaxColumnsPerBoard($this->route('board'))],
        ];
    }
}
