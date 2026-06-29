<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\api\BusinessController;
use App\Http\Controllers\api\ReviewController;

Route::post('/register', Register::class);

Route::get('/businesses', [BusinessController::class, 'index']);

Route::get('/businesses/{slug}', [BusinessController::class, 'show']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'name'  => $user->firstName . ' ' . $user->lastName,
        'email' => $user->email,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-businesses', [BusinessController::class, 'myBusinesses']);
    Route::post('/businesses', [BusinessController::class, 'store']);
    Route::get('/businesses/{slug}', [BusinessController::class, 'show']);
    Route::post('/businesses/{slug}', [BusinessController::class, 'update']);
    Route::delete('/businesses/image/{imageId}', [BusinessController::class, 'deleteImage']);
    Route::get('/businesses/{slug}/reviews',          [ReviewController::class, 'index']);
    Route::post('/businesses/{slug}/reviews',         [ReviewController::class, 'store']);
    Route::delete('/businesses/{slug}/reviews/{reviewId}',  [ReviewController::class, 'destroy']);
});

// Route::get('/view-all', function() {
//     return App\Models\User::all();
// });
//
// Route::get('/all-businesses', function() {
//     return App\Models\Business::all();
// });
//
// Route::post('/register', Register::class);
// Route::post('/createBusiness', BusinessController::class);
//
// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });
