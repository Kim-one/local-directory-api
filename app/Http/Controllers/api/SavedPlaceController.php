<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\SavedPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedPlaceController extends Controller
{
    public function index()
    {
        $savedPlaces = SavedPlace::with('business.images')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($savedPlaces);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);

        $existing = SavedPlace::where('user_id', Auth::id())
            ->where('business_id', $validated['business_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already saved.'], 409);
        }

        $savedPlace = SavedPlace::create([
            'user_id' => Auth::id(),
            'business_id' => $validated['business_id'],
        ]);

        return response()->json([
            'message' => 'Business saved.',
            'savedPlace' => $savedPlace,
        ], 201);
    }

    public function destroy(int $businessId)
    {
        $deleted = SavedPlace::where('user_id', Auth::id())
            ->where('business_id', $businessId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Already deleted.'], 404);
        }

        return response()->json(['message' => 'Business unsaved.']);
    }
}
