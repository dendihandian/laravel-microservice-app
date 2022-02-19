<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // $user->createToken($request->email);

        return response()->json([
            'message' => 'User registered successfuly'
        ], 200);
    }

    public function login(LoginRequest $request)
    {
        $result = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$result) {
            return response()->json([
                'message' => 'Wrong credentials'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ], 200);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => UserResource::make($request->user()),
        ], 200);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ], 200);
    }

    public function revokeToken(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'token revoked',
        ], 200);
    }
}
