<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleWare;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware(ApiAuthMiddleWare::class)->group(function () {
    // User
    Route::get('/users/current', [UserController::class, 'get']);
    Route::patch('/users/current', [UserController::class, 'update']);
    Route::delete('/users/logout', [UserController::class, 'logout']);

    // Contact
    Route::post('/contacts', [ContactController::class, 'create']);
    Route::get('/contacts', [ContactController::class, 'search']);
    Route::get('/contacts/{id}', [ContactController::class, 'get'])->where('id', '[0-9]+');
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/contacts/{id}', [ContactController::class, 'delete'])->where('id', '[0-9]+');

    // Address
    Route::post('/contacts/{contact_id}/addresses', [AddressController::class, 'create'])->where('contact_id', '[0-9]+');
    Route::get('/contacts/{contact_id}/addresses', [AddressController::class, 'list'])->where('contact_id', '[0-9]+');
    Route::get('/contacts/{contact_id}/addresses/{address_id}', [AddressController::class, 'get'])
        ->where('contact_id', '[0-9]+')
        ->where('address_id', '[0-9]+');
    Route::put('/contacts/{contact_id}/addresses/{address_id}', [AddressController::class, 'update'])
        ->where('contact_id', '[0-9]+')
        ->where('address_id', '[0-9]+');
    Route::delete('/contacts/{contact_id}/addresses/{address_id}', [AddressController::class, 'delete'])
        ->where('contact_id', '[0-9]+')
        ->where('address_id', '[0-9]+');
});
