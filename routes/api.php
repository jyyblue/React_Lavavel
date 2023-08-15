<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SellerController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/getGoogleSeller', [SellerController::class, 'getGoogleSeller']);
    Route::post('/updateGoogleSeller', [SellerController::class, 'updateGoogleSeller']);
    Route::get('googleSeller/export', [SellerController::class, 'exportGoogleSeller']);
    Route::post('googleSeller/import', [SellerController::class, 'importGoogleSeller']);

    Route::post('/getAmazonSeller', [SellerController::class, 'getAmazonSeller']);
    Route::post('/updateAmazonSeller', [SellerController::class, 'updateAmazonSeller']);
    Route::get('amazonSeller/export', [SellerController::class, 'exportAmazonSeller']);
    Route::post('amazonSeller/import', [SellerController::class, 'importAmazonSeller']);
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
