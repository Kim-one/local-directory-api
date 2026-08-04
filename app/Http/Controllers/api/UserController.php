<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'firstName'    => 'sometimes|string|max:100',
            'lastName'     => 'sometimes|string|max:100',
            'email'        => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'parish'       => 'sometimes|string|max:100',
            'country'      => 'sometimes|string|max:100',
            'avatar'       => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
            'removeAvatar' => 'sometimes|boolean',
        ]);

        // Don't try to mass-assign the file / control fields.
        unset($validated['avatar'], $validated['removeAvatar']);

        // Handle a new avatar upload.
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('s3')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('users/avatars', 's3');
        } elseif ($request->boolean('removeAvatar') && $user->avatar) {
            // Explicit removal with no replacement.
            Storage::disk('s3')->delete($user->avatar);
            $validated['avatar'] = null;
        }

        $user->update($validated);

        return response()->json([
            'id'       => $user->id,
            'name'     => $user->firstName . ' ' . $user->lastName,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'email'    => $user->email,
            'parish'   => $user->parish,
            'country'  => $user->country,
            'avatar'   => $user->avatar,
            'is_admin' => (bool) $user->is_admin,
        ]);
    }
}
