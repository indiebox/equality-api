<?php

namespace App\Rules\Api;

use App\Models\Project;
use Illuminate\Contracts\Validation\ImplicitRule;

class MaxBoardsPerProject implements ImplicitRule
{
    /** Max boards per project.*/
    public const MAX_BOARDS = 10;

    protected $project;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
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
        return $this->project->boards()->count() < static::MAX_BOARDS;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.max_boards_per_project', ['max' => static::MAX_BOARDS]);
    }
}
