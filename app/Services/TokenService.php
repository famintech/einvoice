<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Platform\LoginTaxpayer;
use App\Http\Controllers\Platform\LoginIntermediary;

class TokenService
{
    private $loginTaxpayer;
    private $loginIntermediary;

    public function __construct(LoginTaxpayer $loginTaxpayer, LoginIntermediary $loginIntermediary)
    {
        $this->loginTaxpayer = $loginTaxpayer;
        $this->loginIntermediary = $loginIntermediary;
    }

    public function getToken($userType, $userId)
    {
        $key = "token:{$userType}:{$userId}";
        Log::info('Attempting to get token', ['key' => $key]);

        try {
            $tokenData = Redis::get($key);
            Log::info('Redis get result', ['tokenData' => $tokenData ? 'Found' : 'Not Found']);

            if (!$tokenData) {
                Log::info('Token not found in Redis, fetching new token');
                $tokenData = $this->fetchNewToken($userType);
                Log::info('New token fetched', ['tokenData' => $tokenData ? 'Success' : 'Failed']);

                if ($tokenData && isset($tokenData['expires_in'])) {
                    Log::info('Attempting to store new token in Redis');
                    $result = Redis::setex($key, $tokenData['expires_in'], json_encode($tokenData));
                    Log::info('Redis setex result', ['result' => $result]);
                } else {
                    Log::error('Invalid token data', ['tokenData' => $tokenData]);
                }
            } else {
                Log::info('Token found in Redis');
                $tokenData = json_decode($tokenData, true);
            }

            return $tokenData;
        } catch (\Exception $e) {
            Log::error('Error in getToken method', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fetchNewToken($userType)
    {
        if ($userType === 'taxpayer') {
            $response = $this->loginTaxpayer->getAccessToken();
        } elseif ($userType === 'intermediary') {
            $response = $this->loginIntermediary->getAccessToken();
        } else {
            throw new \InvalidArgumentException("Invalid user type: {$userType}");
        }

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response->getData(true);
        }

        return $response;
    }
}
