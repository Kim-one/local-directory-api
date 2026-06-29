<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Logout extends Controller
{
    public function __invoke(Request $request)
    {
        // Revoke the token that was used to authenticate
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User successfully logged out'
        ], 200);
    }
}
