<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreBoardRequest;
use App\Http\Resources\V1\Project\ProjectBoardResource;
use App\Models\Board;
use App\Models\Project;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        return ProjectBoardResource::collection($project->boards);
    }

    /**
     * Display a listing of the trashed resource.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function indexTrashed(Project $project)
    {
        return ProjectBoardResource::collection($project->boards()->onlyTrashed()->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBoardRequest  $request
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBoardRequest $request, Project $project)
    {
        $board = new Board($request->validated());
        $board->project()->associate($project);
        $board->save();

        return (new ProjectBoardResource($board))->response()->setStatusCode(201);
    }
}
