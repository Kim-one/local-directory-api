<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'firstName' => 'sometimes|string|max:100',
            'lastName'  => 'sometimes|string|max:100',
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'parish'    => 'sometimes|string|max:100',
            'country'   => 'sometimes|string|max:100',
        ]);

        $user->update($validated);

        return response()->json([
            'id' => $user->id,
            'name' => $user->firstName . ' ' . $user->lastName,
            'email' => $user->email,
            'parish' => $user->parish,
            'country' => $user->country
        ]);
    }
}
