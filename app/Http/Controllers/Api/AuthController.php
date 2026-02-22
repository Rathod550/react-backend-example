<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        \Log::info($request->all());
        // validate
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // find user
        $user = User::where('email', $request->email)->first();

        // check password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        // IMPORTANT: delete old tokens (single device login)
        $user->tokens()->delete();

        // create new token
        $token = $user->createToken('react-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }


    public function logout(Request $request)
    {
        // delete the token used in this request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}