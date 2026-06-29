<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // validate input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // if login attempt is made
        if (Auth::attempt($credentials)){
            // generate session security
            $request->session()->regenerate();
            $user = Auth::user();

            return response()->json([
                'message' => "Welcome Back",
                'user' => [
                    'name' => $user->firstName.' '.$user->lastName,
                    'email' => $user->email,
                ]
            ]);
        }

        // if fails
        return response()->json([
            'message' => 'Invalid Username or Password'
        ], 401);
    }
}
