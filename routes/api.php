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

Route::get('/test', [TestingController::class, 'index']);

Route::get('/test-redis', function () {
    Redis::set('test_key', 'Hello, Redis!');
    return Redis::get('test_key');
});

Route::get('/config-check', function () {
    return [
        'app_env' => Config::get('app.env'),
        'app_debug' => Config::get('app.debug'),
        'log_channel' => Config::get('logging.default'),
        'redis_host' => Config::get('database.redis.default.host'),
        'redis_port' => Config::get('database.redis.default.port'),
    ];
});

Route::get('/test-redis-storage', function () {
    $testData = ['key' => 'value', 'timestamp' => time()];
    Redis::set('test_data', json_encode($testData));
    $retrieved = Redis::get('test_data');
    return [
        'stored' => $testData,
        'retrieved' => json_decode($retrieved, true)
    ];
});

Route::get('/test-token-storage', function (TokenService $tokenService) {
    Log::info('Test token storage route hit');

    $testToken = [
        'access_token' => 'test_token_' . time(),
        'expires_in' => 3600,
        'token_type' => 'Bearer',
        'scope' => 'InvoicingAPI'
    ];

    Log::info('Attempting to store test token', ['testToken' => $testToken]);

    $key = 'token:taxpayer:test_user';
    $result = Redis::setex($key, 3600, json_encode($testToken));

    Log::info('Redis setex result', ['result' => $result]);

    $storedToken = Redis::get($key);

    Log::info('Retrieved token from Redis', ['storedToken' => $storedToken]);

    return [
        'stored_token' => json_decode($storedToken, true),
        'test_token' => $testToken,
        'redis_set_result' => $result
    ];
});

Route::post('/get-access-token', [LoginTaxpayer::class, 'login']);
