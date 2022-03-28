<?php

namespace App\Http\Requests\Api\V1\Board;

use App\Rules\Api\MaxBoardsPerProject;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoardRequest extends FormRequest
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
            'project' => [new MaxBoardsPerProject($this->route('project'))],
        ];
    }
}
