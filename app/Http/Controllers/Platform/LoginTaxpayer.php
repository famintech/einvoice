<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Http\Request;
use App\Services\TokenService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class LoginTaxpayer extends Controller
{
    private $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function login(Request $request)
    {

        return response()->json([
            'message' => 'This is a test response',
            'received_data' => $request->all()
        ])
        ->header('Content-Type', 'application/json')
        ->header('X-Debug-Info', 'Response sent from LoginTaxpayer controller');

        // $request->validate([
        //     'userId' => 'required|string',
        //     'userType' => 'required|string|in:taxpayer,intermediary',
        // ]);

        // $userId = $request->input('userId');
        // $userType = $request->input('userType');

        // $tokenData = $this->tokenService->getToken($userType, $userId);

        // if ($tokenData && isset($tokenData['access_token'])) {
        //     return response()->json([
        //         'access_token' => $tokenData['access_token'],
        //         'expires_in' => $tokenData['expires_in'],
        //         'token_type' => $tokenData['token_type'],
        //         'scope' => $tokenData['scope'],
        //     ]);
        // } else {
        //     return response()->json(['error' => 'Failed to obtain access token'], 500);
        // }
    }

    public function getAccessToken()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip,deflate,br',
            'Connection' => 'keep-alive',
        ])->asForm()->post(config('api.base_url') . '/connect/token', [
            'client_id' => config('api.client_id'),
            'client_secret' => config('api.client_secret'),
            'grant_type' => 'client_credentials',
            'scope' => 'InvoicingAPI',
        ]);

        if ($response->successful()) {
            return $response->json();
        }
    
        return response()->json([
            'error' => 'Failed to obtain access token',
            'status' => $response->status(),
            'body' => $response->body(),
        ], $response->status());
    }
}