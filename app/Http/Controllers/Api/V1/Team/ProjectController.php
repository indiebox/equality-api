<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreProjectRequest;
use App\Http\Resources\V1\Team\TeamProjectResource;
use App\Models\Project;
use App\Models\Team;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Team $team)
    {
        return TeamProjectResource::collection($team->projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Project\StoreProjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request, Team $team)
    {
        $project = new Project($request->validated());
        $project->team()->associate($team);
        $project->leader()->associate($request->user());
        $project->save();

        return (new TeamProjectResource($project))->response()->setStatusCode(201);
    }
}
