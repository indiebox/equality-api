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
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        if (!QueryBuilder::hasInclude('team')) {
            $project->unsetRelation('team');
        }

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

        return new ProjectResource($project);
    }

    public function restore(Project $project)
    {
        $project->restore();

        return new ProjectResource($project);
    }
}
