<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::get('/app', [AppController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Breeze account management (name/email/password)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // RestaurantAI dietary profile + recommendations actions
    Route::post('/app/dietary-profile', [AppController::class, 'updateDietaryProfile'])->name('app.dietary-profile');
    Route::post('/app/recommendations/analyze/{plate}', [AppController::class, 'analyze'])->name('app.recommendations.analyze');
});

Route::view('/docs', 'docs');
Route::view('/demo-old', 'demo');

require __DIR__.'/auth.php';
