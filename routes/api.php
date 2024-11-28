<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionsController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
 

    // Authentication Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Authenticated Wallet Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Wallet Endpoints
        Route::get('/wallet/balance', [WalletController::class, 'checkBalance']);
        Route::post('/wallet/fund', [WalletController::class, 'fundWallet']);
        //Tranactions Endpoints
        Route::get('/wallet/transactions/all', [TransactionsController::class, 'getAllTransactions']);
        Route::get('/wallet/transactions/deposit', [TransactionsController::class, 'getFundTransactions']);
        Route::get('/wallet/transactions/purchases', [TransactionsController::class, 'getAirtimeTransactions']);
        
        // Airtime Endpoints
        Route::get('/airtime/providers', [WalletController::class, 'getSupportedProviders']);
        Route::post('/wallet/airtime', [WalletController::class, 'airtimePurchase']);
        // Logout Endpoint
        Route::post('/logout', [AuthController::class, 'logout']);
    });