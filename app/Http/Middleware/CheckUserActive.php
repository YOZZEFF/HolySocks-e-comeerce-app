<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(auth()->check()){

        if(!auth()->user()->is_active){


            return response()->json([

            'status' => false,
            'message' => 'Account suspended',
            ],403);
        }
        }
        return $next($request);
    }
}
