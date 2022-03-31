<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserResource;
use App\Services\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index()
    {
        $user = QueryBuilder::for(auth()->user())
            ->allowedFields(UserResource::allowedFields(), UserResource::defaultFields())
            ->get();

        return new UserResource($user);
    }
}
