<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class AdminBusinessController extends Controller
{
    /**
     * List businesses for the admin dashboard, optionally filtered by status.
     * GET /api/admin/businesses?status=pending|approved|rejected|all
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = Business::with('images', 'socialLinks', 'hours', 'user')
            ->orderBy('created_at', 'desc');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        return response()->json($query->get());
    }

    /**
     * Counts per status, for dashboard badges.
     * GET /api/admin/businesses/stats
     */
    public function stats()
    {
        return response()->json([
            'pending'  => Business::where('status', 'pending')->count(),
            'approved' => Business::where('status', 'approved')->count(),
            'rejected' => Business::where('status', 'rejected')->count(),
            'total'    => Business::count(),
        ]);
    }

    /**
     * Approve a business so it appears on the public discovery page.
     * PATCH /api/admin/businesses/{id}/approve
     */
    public function approve(int $id)
    {
        $business = Business::findOrFail($id);

        $business->update([
            'status'           => 'approved',
            'rejection_reason' => null,
            'reviewed_at'      => now(),
        ]);

        return response()->json([
            'message'  => 'Business approved.',
            'business' => $business->load('images', 'socialLinks', 'hours', 'user'),
        ]);
    }

    /**
     * Reject a business, optionally with a reason for the owner.
     * PATCH /api/admin/businesses/{id}/reject
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $business = Business::findOrFail($id);

        $business->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_at'      => now(),
        ]);

        return response()->json([
            'message'  => 'Business rejected.',
            'business' => $business->load('images', 'socialLinks', 'hours', 'user'),
        ]);
    }
}
