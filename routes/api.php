<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SettingController;

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

    Route::post('/getTopAmazon', [SellerController::class, 'getTopAmazon']);
    Route::post('/sendAmazonMail', [SellerController::class, 'sendAmazonMail']);
    Route::post('/getAmazonMail', [SellerController::class, 'getAmazonMail']);

    Route::post('/getTopGoogle', [SellerController::class, 'getTopGoogle']);
    Route::post('/sendGoogleMail', [SellerController::class, 'sendGoogleMail']);
    Route::post('/getGoogleMail', [SellerController::class, 'getGoogleMail']);

    Route::post('/setting/getSetting', [SettingController::class, 'getSetting']);
    Route::post('/setting/updateSetting', [SettingController::class, 'updateSetting']);

    Route::post('/history/getAmazonScrapeHistory', [SettingController::class, 'getAmazonScrapeHistory']);
    Route::post('/history/getGoogleScrapeHistory', [SettingController::class, 'getGoogleScrapeHistory']);
});

