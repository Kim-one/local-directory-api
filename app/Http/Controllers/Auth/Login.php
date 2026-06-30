<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid Username or Password'
            ], 401);
        }

        $user = Auth::user();

        // Delete old tokens (optional but clean)
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Welcome Back',
            'token' => $token,
            'user' => [
                'name' => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
                'parish' => $user->parish,
                'country' => $user->country
            ]
        ]);
    }
}
