<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class LoginTaxpayer extends Controller
{
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
            $headers = $response->headers();
            
            // Pass through all X-Rate-Limit headers
            $relevantHeaders = array_filter($headers, function($key) {
                return strpos(strtolower($key), 'x-rate-limit') === 0;
            }, ARRAY_FILTER_USE_KEY);
    
            return response()->json($response->json())
                ->withHeaders($relevantHeaders);
        }
    
        return response()->json([
            'error' => 'Failed to obtain access token',
            'status' => $response->status(),
            'body' => $response->body(),
        ], $response->status())
        ->withHeaders($response->headers());
    }
}