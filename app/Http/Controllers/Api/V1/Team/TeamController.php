<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Events\Api\UserLeaveTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreTeamRequest;
use App\Http\Requests\Api\V1\Team\UpdateTeamRequest;
use App\Http\Resources\V1\Team\TeamMemberResource;
use App\Http\Resources\V1\Team\TeamResource;
use App\Models\Team;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TeamResource::collection(auth()->user()->teams);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
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

        return (new TeamResource($team));
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
