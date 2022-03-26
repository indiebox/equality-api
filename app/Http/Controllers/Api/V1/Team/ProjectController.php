<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreProjectRequest;
use App\Http\Resources\V1\Team\TeamProjectResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\Project;
use App\Models\Team;
use App\Services\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Team $team)
    {
        $projects = QueryBuilder::for($team->projects())
            ->allowedFields(
                [TeamProjectResource::class, UserResource::class => 'leader'],
                [TeamProjectResource::class, UserResource::class => 'leader']
            )
            ->allowedIncludes('leader')
            ->get();

        return TeamProjectResource::collection($projects);
    }

    /**
     * Display a listing of the trashed resource.
     *
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\Response
     */
    public function indexTrashed(Team $team)
    {
        $projects = QueryBuilder::for($team->projects()->onlyTrashed())
            ->allowedFields(
                [TeamProjectResource::class],
                [TeamProjectResource::class]
            )
            ->get();

        return TeamProjectResource::collection($projects);
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
        $project->leader()->associate(auth()->user());
        $project->save();

        $project->leaderNominations()->create([
            'voter_id' => auth()->id(),
            'nominated_id' => auth()->id(),
        ]);

        return (new TeamProjectResource($project))->response()->setStatusCode(201);
    }
}
