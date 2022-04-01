<?php

namespace App\Http\Requests\Api\V1\Board;

use App\Rules\Api\MaxBoardsPerProject;
use Illuminate\Foundation\Http\FormRequest;

class OpenBoardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $board = $this->route('closed:board');
        $project = $board->relationLoaded('project')
            ? $board->project
            : $board->project()->withoutGlobalScopes()->first();

        return [
            'project' => [new MaxBoardsPerProject($project)],
        ];
    }
}
