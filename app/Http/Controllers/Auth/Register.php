<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'email'     => 'required|string|email|max:555|unique:users',
            'address'   => 'string|max:155',
            'password'  => 'required|string|min:10',
        ]);

        $user = User::create([
            'firstName' => $validated['firstName'],
            'lastName'  => $validated['lastName'],
            'email'     => $validated['email'],
            'address'   => $validated['address'] ?? '',
            'password'  => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => "Welcome, $user->firstName",
            'token'   => $token,
            'user'    => [
                'name'  => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
            ]
        ], 201);
    }
}
