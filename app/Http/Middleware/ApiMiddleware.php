<?php

namespace App\Http\Middleware;

use Closure;

class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $res = $next($request);
        $res->header('Access-Control-Allow-Origin', '*');
        $res->header('Access-Control-Allow-Methods', 
            'GET, POST, PUT, PATCH, DELETE, OPTIONS'
        );
        $res->header('Access-Control-Allow-Headers', 
            'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
        );
        return $res;
    }
}
