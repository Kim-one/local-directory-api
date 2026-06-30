<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessImage;
use App\Models\BusinessHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function store(Request $data)
    {
        $validated = $data->validate([
            'businessName'          => 'required|string|max:100',
            'category'              => 'required|string|max:100',
            'slogan'                => 'nullable|string|max:100',
            'description'           => 'required|string|max:550',
            'streetAddress'         => 'required|string|max:100',
            'parish'                => 'required|string|max:100',
            'city'                  => 'required|string|max:100',
            'about'                 => 'nullable|string',
            'phone'                 => 'required|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'website'               => 'nullable|url|max:255',
            'heroImage'             => 'required|image|mimes:jpeg,png,webp|max:4096',
            'galleryImages'         => 'nullable|array|max:8',
            'galleryImages.*'       => 'image|mimes:jpeg,png,webp|max:4096',
            'socialLinks'           => 'nullable|array',
            'socialLinks.instagram' => 'nullable|url|max:255',
            'socialLinks.facebook'  => 'nullable|url|max:255',
            'socialLinks.twitter'   => 'nullable|url|max:255',
            'hours'                 => 'nullable|array',
            'hours.*.day'           => 'required_with:hours|string',
            'hours.*.open_time'     => 'nullable|date_format:H:i',
            'hours.*.close_time'    => 'nullable|date_format:H:i',
            'hours.*.is_closed'     => 'nullable|boolean',
        ]);

        $validated['slug']        = Str::slug($validated['businessName']);
        $validated['user_id']     = Auth::id();
        $validated['featured']    = false;
        $validated['verified']    = false;
        $validated['rating']      = 0;
        $validated['reviewCount'] = 0;

        unset($validated['heroImage'], $validated['galleryImages'], $validated['socialLinks'], $validated['hours']);

        // Create business FIRST, then use $business below
        $business = Business::create($validated);

        // Hero image
        $heroPath = $data->file('heroImage')->store('businesses/hero', 's3');
        $business->images()->create(['path' => $heroPath, 'type' => 'hero']);

        // Gallery images
        if ($data->hasFile('galleryImages')) {
            foreach ($data->file('galleryImages') as $image) {
                $path = $image->store('businesses/gallery', 's3');
                $business->images()->create(['path' => $path, 'type' => 'gallery']);
            }
        }

        // Social links
        $socialLinks = $data->input('socialLinks', []);
        foreach ($socialLinks as $platform => $url) {
            if (!empty($url)) {
                $business->socialLinks()->create([
                    'platform' => $platform,
                    'url'      => $url,
                ]);
            }
        }

        // Hours
        $hours = $data->input('hours', []);
        foreach ($hours as $hour) {
            $business->hours()->create([
                'day'        => $hour['day'],
                'open_time'  => ($hour['is_closed'] ?? false) ? null : ($hour['open_time'] ?? '09:00'),
                'close_time' => ($hour['is_closed'] ?? false) ? null : ($hour['close_time'] ?? '17:00'),
                'is_closed'  => $hour['is_closed'] ?? false,
            ]);
        }

        return response()->json([
            'message'  => 'Business created successfully.',
            'business' => $business->load('images', 'socialLinks', 'hours'),
        ], 201);
    }

    public function myBusinesses(Request $request)
    {
        $businesses = Business::with('images', 'socialLinks', 'hours')
            ->where('user_id', Auth::id())
            ->get();
        return response()->json($businesses);
    }

    public function index(Request $request)
    {
        $businesses = Business::with('images', 'socialLinks', 'hours')->get();
        return response()->json($businesses);
    }

    public function category(string $category)
    {
        $businesses = Business::with('images', 'socialLinks', 'hours')
            ->where('category', $category)
            ->get();

        return response()->json($businesses);
    }

    public function parishes(string $parish)
    {
        $businesses = Business::with('images', 'socialLinks', 'hours')
            ->where('parish', $parish)
            ->get();

        return response()->json($businesses);
    }

    public function show(string $slug)
    {
        $business = Business::where('slug', $slug)
            ->with('images', 'socialLinks', 'hours')
            ->firstOrFail();
        return response()->json($business);
    }

    public function update(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        if ($business->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // $request->validate(), not $data->validate()
        $validated = $request->validate([
            'businessName'          => 'sometimes|string|max:100',
            'category'              => 'sometimes|string|max:100',
            'description'           => 'sometimes|string|max:550',
            'streetAddress'         => 'sometimes|string|max:100',
            'parish'                => 'sometimes|string|max:100',
            'city'                  => 'sometimes|string|max:100',
            'about'                 => 'nullable|string',
            'phone'                 => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'website'               => 'nullable|url|max:255',
            'heroImage'             => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'galleryImages'         => 'nullable|array|max:8',
            'galleryImages.*'       => 'image|mimes:jpeg,png,webp|max:4096',
            'socialLinks'           => 'nullable|array',
            'socialLinks.instagram' => 'nullable|url|max:255',
            'socialLinks.facebook'  => 'nullable|url|max:255',
            'socialLinks.twitter'   => 'nullable|url|max:255',
            'hours'                 => 'nullable|array',
            'hours.*.day'           => 'required_with:hours|string',
            'hours.*.open_time'     => 'nullable|date_format:H:i',
            'hours.*.close_time'    => 'nullable|date_format:H:i',
            'hours.*.is_closed'     => 'nullable|boolean',
        ]);

        if (isset($validated['businessName'])) {
            $validated['slug'] = Str::slug($validated['businessName']);
        }

        unset($validated['heroImage'], $validated['galleryImages'], $validated['socialLinks'], $validated['hours']);

        $business->update($validated);

        // Hero image
        if ($request->hasFile('heroImage')) {
            $existing = $business->images()->where('type', 'hero')->first();
            if ($existing) {
                Storage::disk('s3')->delete($existing->path);
                $existing->delete();
            }
            $heroPath = $request->file('heroImage')->store('businesses/hero', 's3');
            $business->images()->create(['path' => $heroPath, 'type' => 'hero']);
        }

        // Gallery images
        if ($request->hasFile('galleryImages')) {
            foreach ($request->file('galleryImages') as $image) {
                $path = $image->store('businesses/gallery', 's3');
                $business->images()->create(['path' => $path, 'type' => 'gallery']);
            }
        }

        // Social links — replace all
        $socialLinks = $request->input('socialLinks', []);
        $business->socialLinks()->delete();
        foreach ($socialLinks as $platform => $url) {
            if (!empty($url)) {
                $business->socialLinks()->create([
                    'platform' => $platform,
                    'url'      => $url,
                ]);
            }
        }

        // Hours — replace all
        $hours = $request->input('hours', []);
        $business->hours()->delete();
        foreach ($hours as $hour) {
            $business->hours()->create([
                'day'        => $hour['day'],
                'open_time'  => ($hour['is_closed'] ?? false) ? null : ($hour['open_time'] ?? '09:00'),
                'close_time' => ($hour['is_closed'] ?? false) ? null : ($hour['close_time'] ?? '17:00'),
                'is_closed'  => $hour['is_closed'] ?? false,
            ]);
        }

        return response()->json([
            'message'  => 'Business updated successfully.',
            'business' => $business->load('images', 'socialLinks', 'hours'),
            'slug'     => $business->slug,
        ]);
    }

    public function deleteImage(Request $request, int $imageId)
    {
        $image = BusinessImage::findOrFail($imageId);

        if ($image->business->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Storage::disk('s3')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted.']);
    }

    public function delete(Request $request, int $businessId)
    {
        $business = Business::with('images', 'hours', 'socialLinks')
            ->where('id', $businessId)
            ->firstOrFail();

        if ($business->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Delete files from S3
        foreach ($business->images as $image) {
            Storage::disk('s3')->delete($image->path);
        }

        // Delete related database records
        $business->images()->delete();
        $business->hours()->delete();
        $business->socialLinks()->delete();

        // Delete business
        $business->delete();

        return response()->json([
            'message' => 'Business deleted successfully.'
        ]);
    }
}
