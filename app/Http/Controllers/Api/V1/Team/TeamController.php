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
        $teams = QueryBuilder::for(
            auth()->user()->teams()
                ->select(['*', 'team_user.joined_at as joined_at'])
        )->allowedFields([TeamResource::class], [TeamResource::class])
            ->allowedSorts(['created_at', 'joined_at', AllowedSort::custom('members_count', new SortRelationsCount('members'))])
            ->defaultSorts('-joined_at')
            ->allowedIncludes(['members_count'])
            ->allowCursorPagination()
            ->cursorPaginate();

        return TeamResource::collection($teams);
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
            ->allowedFields(
                [
                    TeamResource::class,
                    TeamMemberResource::class,
                ],
                [
                    TeamResource::class,
                    TeamMemberResource::class,
                ],
            )
            ->allowedIncludes(['members'])
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
        $members = QueryBuilder::for(
            $team->members()
                ->select(['*', 'team_user.joined_at as joined_at'])
        )->allowedFields([TeamMemberResource::class], [TeamMemberResource::class], 'members')
            ->allowedSorts(['joined_at'])
            ->defaultSorts('-joined_at')
            ->allowCursorPagination()
            ->cursorPaginate(25);

        return TeamMemberResource::collection($members);
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

        $team = QueryBuilder::for($team)
            ->allowedFields([TeamResource::class], [TeamResource::class])
            ->get();

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

        $team = QueryBuilder::for($team)
            ->allowedFields([TeamResource::class], [TeamResource::class])
            ->get();

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
