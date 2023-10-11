<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserRegisterRequest;
use Illuminate\Database\Eloquent\Casts\Json;
use illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserController extends Controller
{
    /**
     * Register a new data of the resource.
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User::where('username', $data['username'])->count() == 1) {
            throw new HttpResponseException(
                response(
                    [
                        'errors' => [
                            'username' => ['username already registered'],
                        ],
                    ],
                    400,
                ),
            );
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Login user of the resource.
     */
    public function login(UserLoginRequest $request): UserResource
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(
                response(
                    [
                        'error' => [
                            'message' => ['login gagal, silahkan cek kembali username / password'],
                        ],
                    ],
                    401,
                ),
            );
        }
        $user->token = Str::uuid()->toString();
        $user->save();

        return new UserResource($user);
    }

    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = Auth::user();

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['password']) && $user->password != Hash::make($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->token = null;
        $user->save();

        return response()->json(['data' => true])->setStatusCode(200);
    }
}
