<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserInviteResource;
use App\Models\Invite;
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
        $invites = auth()->user()->invites()->sortByStatus($request->query('filter', 'all'))->get();

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
