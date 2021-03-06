<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\UpdateProjectRequest;
use App\Http\Resources\V1\Project\ProjectResource;
use App\Http\Resources\V1\Team\TeamResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\Project;
use App\Services\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = QueryBuilder::for(Project::whereIn('team_id', auth()->user()->teams()->pluck('teams.id')))
            ->allowedFields(
                [ProjectResource::class, UserResource::class => 'leader', TeamResource::class => 'team'],
                [ProjectResource::class, UserResource::class => 'leader', TeamResource::class => 'team']
            )
            ->allowedIncludes(['leader', 'team'])
            ->allowedFilters('name')
            ->allowedSorts(['created_at', 'updated_at'])
            ->defaultSorts('-updated_at')
            ->allowCursorPagination()
            ->cursorPaginate();

        return ProjectResource::collection($projects);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $project = QueryBuilder::for($project)
            ->allowedFields([
                ProjectResource::class,
                UserResource::class => 'leader',
                TeamResource::class => 'team',
            ], [
                ProjectResource::class,
                UserResource::class => 'leader',
                TeamResource::class => 'team',
            ])
            ->allowedIncludes(['leader', 'team'])
            ->get();

        return new ProjectResource($project);
    }

    public function leader(Project $project)
    {
        $leader = QueryBuilder::for($project->leader)
            ->allowedFields([UserResource::class => 'leader'], [UserResource::class => 'leader'], 'leader')
            ->get();

        return new UserResource($leader);
    }

    public function team(Project $project)
    {
        $team = QueryBuilder::for($project->team)
            ->allowedFields([TeamResource::class => 'team'], [TeamResource::class => 'team'], 'team')
            ->get();

        return new TeamResource($team);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Project\UpdateProjectRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        $project = QueryBuilder::for($project)
            ->allowedFields([ProjectResource::class], [ProjectResource::class])
            ->get();

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response('', 204);
    }

    public function restore(Project $project)
    {
        $project->restore();

        return response('', 204);
    }
}
