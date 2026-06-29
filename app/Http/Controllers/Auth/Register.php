<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // validate input
        $validated = $request->validate([
            'firstName' => 'string|max:255',
            'lastName' => 'string|max:255',
            'email' => 'string|email|max:555',
            'address' => 'string|max:155',
            'password' => 'string|min:10',
        ]);

        // create user
        $user = User::create([
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return response()->json([
            'message' => "Welcome, $user->firstName",
            'user' => [
                'name' => $user->firstName . ' ' . $user->lastName,
                'email' => $user->email,
            ]
        ], 201);
    }
}
