<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VendorController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/usuarios/{id}', [AuthController::class, 'show']);
Route::get('/vendedores', [VendorController::class, 'index']);
Route::get('/vendedores/{id}', [VendorController::class, 'show']);
Route::get('/usuarios-masivos', [AuthController::class, 'usuariosMasivos']);


Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/profile', [AuthController::class, 'me']);
     Route::patch('/profile/update', [AuthController::class, 'update']);
});

Route::get('/validate', function () {
    try {
        $user = auth()->userOrFail(); // usando jwt.auth
        return response()->json([
            'valid' => true,
            'user'  => $user
        ]);
    } catch (\Exception $e) {
        return response()->json(['valid' => false], 401);
    }
})->middleware('jwt.auth');
