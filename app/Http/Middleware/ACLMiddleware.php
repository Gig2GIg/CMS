<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;


class ACLMiddleware
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $typeUser)
    { $token =  JWTAuth::parseToken();
        $token->authenticate();

        $typeToken = $token->getPayload()->get('type');


            if($typeUser === $typeToken){
                   return $next($request);
            }


        return response()->json(['error' => 'Unauthorized Type'], 401);
    }
}
