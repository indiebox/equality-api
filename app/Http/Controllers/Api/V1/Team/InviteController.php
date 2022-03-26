<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\StoreInviteRequest;
use App\Http\Resources\V1\Team\TeamInviteResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\Invite;
use App\Models\Team;
use App\Services\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

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
        $invites = QueryBuilder::for($team->invites())
            ->allowedFilters(AllowedFilter::scope('status', 'filterByStatus')->default('all'))
            ->allowedFields([
                TeamInviteResource::class,
                UserResource::class => 'inviter',
                UserResource::class => 'invited',
            ], [
                TeamInviteResource::class,
                UserResource::class => 'inviter',
                UserResource::class => 'invited',
            ])
            ->allowedIncludes(['inviter', 'invited'], ['inviter', 'invited'])
            ->get();

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
        $invite->invited()->associate($request->invited);
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
