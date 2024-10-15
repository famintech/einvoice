<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Http\Request;
use App\Services\TokenService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class LoginMain extends Controller
{
    private $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function login(Request $request)
    {
        Log::info('Login attempt', $request->all());

        $request->validate([
            'userId' => 'required|string',
            'userType' => 'required|string|in:taxpayer,intermediary',
            'onbehalfof' => 'required_if:userType,intermediary|string',
        ]);

        $userId = $request->input('userId');
        $userType = $request->input('userType');
        $onbehalfof = $request->input('onbehalfof');

        $tokenData = $this->tokenService->getToken($userType, $userId, $onbehalfof);

        if ($tokenData && isset($tokenData['access_token'])) {
            return response()->json([
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'],
                'token_type' => $tokenData['token_type'],
                'scope' => $tokenData['scope'],
            ]);
        } else {
            Log::error('Failed to obtain access token', ['tokenData' => $tokenData]);
            return response()->json(['error' => 'Failed to obtain access token'], 500);
        }
    }
}