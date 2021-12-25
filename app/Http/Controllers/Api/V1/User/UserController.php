<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;

class UserController extends Controller
{
    public function index() {
        return new UserResource(auth()->user());
    }
}
