<?php

namespace app\middleware;

use app\service\JwtService;

class JwtAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return json(['code' => 401, 'message' => 'no token']);
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $user = JwtService::parseToken($token);

            $request->user = $user;

        } catch (\Exception $e) {
            return json(['code' => 401, 'message' => 'invalid token']);
        }

        return $next($request);
    }
}