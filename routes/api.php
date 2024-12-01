<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\nnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum','user.locale'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile', [UserController::class, 'updateProfile']);
    Route::post('/language',[UserController::class,'changeLocale']);
    Route::apiResource('/order', OrderController::class);
});



//for admin(later):
Route::apiResource('/stores',StoreController::class);
Route::get('/users',[UserController::class,'index']);
Route::apiResource('/products',ProductController::class);

Route::prefix('products/{productId}/images')->group(function () {
    Route::post('/', [ImageController::class, 'store']);
    Route::get('/', [ImageController::class, 'index']);
    Route::delete('/{imageId}',[ImageController::class,'delete']);
    Route::get('/{imageId}',[ImageController::class,'show']);


});
// Route::apiResource('/order', OrderController::class);


