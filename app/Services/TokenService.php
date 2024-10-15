<?php

namespace App\Services;

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

    public function getToken($userType, $userId, $onbehalfof = null)
    {
        $key = "token:{$userType}:{$userId}" . ($onbehalfof ? ":{$onbehalfof}" : "");
        $tokenData = Redis::get($key);

        if (!$tokenData) {
            $tokenData = $this->fetchNewToken($userType, $onbehalfof);
            if ($tokenData) {
                // Store the entire token data
                Redis::setex($key, $tokenData['expires_in'], json_encode($tokenData));
            }
        } else {
            $tokenData = json_decode($tokenData, true);
        }

        return $tokenData;
    }

    private function fetchNewToken($userType, $onbehalfof = null)
    {
        if ($userType === 'taxpayer') {
            $response = $this->loginTaxpayer->getAccessToken();
        } elseif ($userType === 'intermediary') {
            $response = $this->loginIntermediary->getAccessToken($onbehalfof);
        } else {
            throw new \InvalidArgumentException("Invalid user type: {$userType}");
        }

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response->getData(true);
        }

        return $response;
    }
}
