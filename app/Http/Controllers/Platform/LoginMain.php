<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Http\Request;
use App\Services\TokenService;
use App\Http\Controllers\Controller;

class LoginMain extends Controller
{
    private $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function login(Request $request)
    {
        return response()->json([
            'message' => 'This is the login method',
            'received_body' => $request->all(),
            'content' => $request->getContent(),
        ]);

        $request->validate([
            'userId' => 'required|string',
            'userType' => 'required|string|in:taxpayer,intermediary',
        ]);

        $userId = $request->input('userId');
        $userType = $request->input('userType');

        $tokenData = $this->tokenService->getToken($userType, $userId);

        if ($tokenData && isset($tokenData['access_token'])) {
            return response()->json([
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'],
                'token_type' => $tokenData['token_type'],
                'scope' => $tokenData['scope'],
            ]);
        } else {
            return response()->json(['error' => 'Failed to obtain access token'], 500);
        }
    }
}