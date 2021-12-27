<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreInviteRequest;
use App\Http\Resources\V1\Team\TeamInviteResource;
use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Team $team)
    {
        $invites = $team->invites()->filterByStatus($request->query('filter', 'all'))->get();

        return TeamInviteResource::collection($invites);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInviteRequest $request, Team $team)
    {
        $invite = new Invite();
        $invite->team()->associate($team);
        $invite->inviter()->associate(auth()->user());
        $invite->invited()->associate(User::where('email', $request->email)->firstOrFail());
        $invite->save();

        return (new TeamInviteResource($invite))->response()->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Invite  $invite
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invite $invite)
    {
        $invite->delete();

        return response('', 204);
    }
}
