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
        Log::info('Login attempt', $request->all());

        try {
            $request->validate([
                'userId' => 'required|string',
                'userType' => 'required|string|in:taxpayer,intermediary',
            ]);

            $userId = $request->input('userId');
            $userType = $request->input('userType');

            Log::info('Fetching token', ['userType' => $userType, 'userId' => $userId]);

            $tokenData = $this->tokenService->getToken($userType, $userId);

            Log::info('Token data received', ['tokenData' => $tokenData ? 'Success' : 'Failed']);

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
        } catch (\Exception $e) {
            Log::error('Exception in login method', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getAccessToken()
    {
        Log::info('Attempting to get access token from external API');
        // Force log to disk
        Log::channel('daily')->info('Forced log entry');
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

        Log::info('Response from external API', [
            'status' => $response->status(),
            'body' => $response->body(),
            'headers' => $response->headers()
        ]);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            Log::info('Successful response parsed', ['jsonResponse' => $jsonResponse]);
            return $jsonResponse;
        }

        Log::error('Failed to obtain access token', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return response()->json([
            'error' => 'Failed to obtain access token',
            'status' => $response->status(),
            'body' => $response->body(),
        ], $response->status());
    }
}
