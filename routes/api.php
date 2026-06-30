<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\api\BusinessController;
use App\Http\Controllers\api\ReviewController;

// Public routes
Route::post('/login', Login::class);
Route::post('/register', Register::class);

Route::get('/businesses', [BusinessController::class, 'index']);
Route::get('/businesses/category/{category}', [BusinessController::class, 'category']);
Route::get('/businesses/{slug}', [BusinessController::class, 'show']);
Route::get('/businesses/{slug}/reviews', [ReviewController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'id'    => $user->id,
            'name'  => $user->firstName . ' ' . $user->lastName,
            'email' => $user->email,
        ]);
    });
    Route::post('/logout', Logout::class);
    Route::get('/my-businesses', [BusinessController::class, 'myBusinesses']);
    Route::post('/businesses', [BusinessController::class, 'store']);
    Route::post('/businesses/{slug}', [BusinessController::class, 'update']);
    Route::delete('/businesses/{businessId}', [BusinessController::class, 'delete']);
    Route::delete('/businesses/image/{imageId}', [BusinessController::class, 'deleteImage']);
    Route::post('/businesses/{slug}/reviews', [ReviewController::class, 'store']);
    Route::delete('/businesses/{slug}/reviews/{reviewId}', [ReviewController::class, 'destroy']);
});
