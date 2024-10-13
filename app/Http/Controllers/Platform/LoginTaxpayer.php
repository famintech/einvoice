<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginTaxpayer extends Controller
{
    public function getAccessToken()
    {
        Log::info('Attempting to get access token');
        Log::info('API Base URL: ' . config('api.base_url'));
        Log::info('Client ID: ' . config('api.client_id'));
        // Don't log the full client secret for security reasons
        Log::info('Client Secret: ' . substr(config('api.client_secret'), 0, 5) . '...');

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

        Log::info('Response status: ' . $response->status());
        Log::info('Response body: ' . $response->body());
    
        if ($response->successful()) {
            return $response->json();
        }
    
        // Return more detailed error information
        return response()->json([
            'error' => 'Failed to obtain access token',
            'status' => $response->status(),
            'body' => $response->body(),
        ], $response->status());
    }
}