<?php

namespace App\Http\Middleware;

use Closure;

class AddCorsHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Access-Control-Expose-Headers', 'X-Rate-Limit-Remaining, X-Rate-Limit-Reset, X-Rate-Limit-Limit');
        
        return $response;
    }
}