<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        // Can't review your own business
        if ($business->user_id === Auth::id()) {
            return response()->json(['message' => 'You cannot review your own business.'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'required|string|max:1000',
        ]);

        // updateOrCreate handles editing an existing review
        $review = Review::updateOrCreate(
            ['business_id' => $business->id, 'user_id' => Auth::id()],
            ['rating' => $validated['rating'], 'body' => $validated['body']]
        );

        // Recalculate business rating and review count
        $avg   = $business->reviews()->avg('rating');
        $count = $business->reviews()->count();
        $business->update([
            'rating'      => round($avg, 1),
            'reviewCount' => $count,
        ]);

        return response()->json([
            'message' => 'Review submitted.',
            'review'  => $review->load('user'),
        ], 201);
    }

    public function index(string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $reviews  = $business->reviews()->with('user')->get();

        return response()->json($reviews);
    }

    public function destroy(string $slug, int $reviewId)
    {
        $review = Review::findOrFail($reviewId);

        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $review->delete();

        // Recalculate after deletion
        $business = Business::where('slug', $slug)->firstOrFail();
        $avg      = $business->reviews()->avg('rating') ?? 0;
        $count    = $business->reviews()->count();
        $business->update([
            'rating'      => round($avg, 1),
            'reviewCount' => $count,
        ]);

        return response()->json(['message' => 'Review deleted.']);
    }
}
