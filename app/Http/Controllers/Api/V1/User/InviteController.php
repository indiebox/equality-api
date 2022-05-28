<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Team\TeamResource;
use App\Http\Resources\V1\User\UserInviteResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\Invite;
use App\Services\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $invites = QueryBuilder::for(auth()->user()->invites()->orderByDesc('updated_at'))
            ->allowedFields([
                UserInviteResource::class,
                TeamResource::class => 'team',
                UserResource::class => 'inviter',
            ], [
                UserInviteResource::class,
                TeamResource::class => 'team',
                UserResource::class => 'inviter',
            ])
            ->allowedIncludes(['team', 'inviter'], ['team', 'inviter'])
            ->allowCursorPagination()
            ->cursorPaginate(10);

        return UserInviteResource::collection($invites);
    }

    /**
     * Accept the specified invite.
     *
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, Invite $invite)
    {
        $invite->accepted_at = now();
        $invite->save();

        $invite->team->members()->attach(auth()->user());

        return response('', 204);
    }

    /**
     * Decline the specified invite.
     *
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Http\Response
     */
    public function decline(Request $request, Invite $invite)
    {
        $invite->declined_at = now();
        $invite->save();

        return response('', 204);
    }
}
