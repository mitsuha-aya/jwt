<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/3/13
 * Time: 14:47
 */

namespace MiTsuHaAya\JWT\Register\Middleware;

use Closure;


class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request,Closure $next)
    {
        $token = $request->bearerToken();



        return $next($request);
    }

}