<?php

use Illuminate\Http\Request;
use App\Services\TokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\Platform\LoginTaxpayer;
use App\Http\Controllers\Platform\LoginIntermediary;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/get-accesstoken', [LoginTaxpayer::class, 'login']);

Route::any('/debug-request', function (Request $request) {
    return response()->json([
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'body' => $request->all(),
        'headers' => $request->headers->all(),
    ]);
});
