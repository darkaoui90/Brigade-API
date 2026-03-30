<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\PlateController;
use App\Http\Controllers\Api\DietaryProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\AdminStatsController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working'
    ]);
});

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/profile', [DietaryProfileController::class, 'show']);
    Route::put('/profile', [DietaryProfileController::class, 'update']);

    Route::get('/admin/stats', AdminStatsController::class)->middleware('admin');

    Route::get('/categories/{category}/plates', [CategoryController::class, 'plates']);
    Route::get('/categories/{category}/plats', [CategoryController::class, 'plates']);

    Route::apiResource('categories', CategoryController::class);

    Route::apiResource('plates', PlateController::class);
    Route::apiResource('plats', PlateController::class)->parameters(['plats' => 'plate']);

    Route::apiResource('ingredients', IngredientController::class)->middleware('admin');

    Route::post('/recommendations/analyze/{plate}', [RecommendationController::class, 'analyze'])->middleware('throttle:5,1');
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/recommendations/{plate}', [RecommendationController::class, 'show']);
});
