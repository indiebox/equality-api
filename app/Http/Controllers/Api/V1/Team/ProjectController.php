<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreProjectRequest;
use App\Http\Resources\V1\Project\ProjectResource;
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
                [ProjectResource::class, UserResource::class => 'leader'],
                [ProjectResource::class, UserResource::class => 'leader']
            )
            ->allowedIncludes('leader')
            ->allowedSorts(['created_at', 'updated_at'])
            ->defaultSorts('-updated_at')
            ->allowCursorPagination()
            ->cursorPaginate();

        return ProjectResource::collection($projects);
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
            ->allowedFields([ProjectResource::class], [ProjectResource::class])
            ->allowedSorts(['created_at', 'deleted_at'])
            ->defaultSorts('-deleted_at')
            ->allowCursorPagination()
            ->cursorPaginate();

        return ProjectResource::collection($projects);
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

        $project = QueryBuilder::for($project)
            ->allowedFields([ProjectResource::class], [ProjectResource::class])
            ->get();

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }
}
