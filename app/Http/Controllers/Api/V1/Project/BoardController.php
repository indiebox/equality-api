<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Board\StoreBoardRequest;
use App\Http\Resources\V1\Board\BoardResource;
use App\Models\Board;
use App\Models\Project;
use App\Services\QueryBuilder\QueryBuilder;

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
        $boards = QueryBuilder::for($project->boards())
            ->allowedFields([BoardResource::class], [BoardResource::class])
            ->get();

        return BoardResource::collection($boards);
    }

    /**
     * Display a listing of the closed resource.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function indexClosed(Project $project)
    {
        $boards = QueryBuilder::for($project->boards()->onlyClosed())
            ->allowedFields([BoardResource::class], [BoardResource::class])
            ->get();

        return BoardResource::collection($boards);
    }

    /**
     * Display a listing of the trashed resource.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function indexTrashed(Project $project)
    {
        $boards = QueryBuilder::for($project->boards()->onlyTrashed())
            ->allowedFields([BoardResource::class], [BoardResource::class])
            ->get();

        return BoardResource::collection($boards);
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

        $board = QueryBuilder::for($board)
            ->allowedFields([BoardResource::class], [BoardResource::class])
            ->get();

        return (new BoardResource($board))->response()->setStatusCode(201);
    }
}
