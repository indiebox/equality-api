<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginUserRequest;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginUserRequest $request) {
        $request->ensureIsNotRateLimited();

        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            $request->hitAttempt();

            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }

        $request->clearAttempts();

        $user->tokens()->where('name', $data['device_name'])->delete();

        return response([
            'data' => new UserResource($user),
            'token' => $user->createToken($data['device_name'])->plainTextToken,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response('', 204);
    }
}
