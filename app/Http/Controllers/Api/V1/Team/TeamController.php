<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Events\Api\UserLeaveTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreTeamRequest;
use App\Http\Requests\Api\V1\Team\UpdateTeamRequest;
use App\Http\Resources\V1\Team\TeamMemberResource;
use App\Http\Resources\V1\Team\TeamResource;
use App\Models\Team;
use App\Services\QueryBuilder\QueryBuilder;
use App\Services\QueryBuilder\Sorts\SortRelationsCount;
use Spatie\QueryBuilder\AllowedSort;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = QueryBuilder::for(auth()->user()->teams()->select(['teams.id', 'name', 'logo']))
            ->allowedFields(TeamResource::$allowedFields + [10 => 'members.name', 11 => 'members.created_at'], ['id', 'name', 'logo', 'members.id', 'members.name'])
            ->allowedSorts(['created_at', AllowedSort::custom('members_count', new SortRelationsCount('members'))])
            ->allowedIncludes('members')
            ->get();

        return TeamResource::collection($query);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        $team = QueryBuilder::for($team)
            ->allowedFields(['members.name', 'members.joined_at', 'id', 'description', 'name'], ['id', 'name', 'logo', 'members.id'])
            ->allowedIncludes('members')
            ->get();

        return new TeamResource($team);
    }

    /**
     * Get all team members.
     *
     * @param \App\Models\Team $team
     */
    public function members(Team $team)
    {
        return TeamMemberResource::collection($team->members);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Team\StoreTeamRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTeamRequest $request)
    {
        $team = Team::create($request->validated());
        $team->members()->attach(auth()->user(), ['is_creator' => true]);

        return (new TeamResource($team))->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Team\UpdateTeamRequest  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team->update($request->validated());

        return new TeamResource($team);
    }

    /**
     * Remove the user from team.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function leave(Team $team)
    {
        $team->members()->detach(auth()->user());

        event(new UserLeaveTeam(auth()->user(), $team));

        return response('', 204);
    }
}
