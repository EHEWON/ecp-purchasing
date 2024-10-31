<?php

namespace App\Middleware;

use Closure;

class Lang {

    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next) {
        app('translator')->setLocale('zh');
        return $next($request);
    }

}
