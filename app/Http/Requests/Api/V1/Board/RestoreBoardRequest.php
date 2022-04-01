<?php

namespace App\Http\Requests\Api\V1\Board;

use App\Rules\Api\MaxBoardsPerProject;
use Illuminate\Foundation\Http\FormRequest;

class RestoreBoardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $board = $this->route('trashed:board');
        $project = $board->relationLoaded('project')
            ? $board->project
            : $board->project()->withoutGlobalScopes()->first();

        return [
            'project' => [new MaxBoardsPerProject($project)],
        ];
    }
}
