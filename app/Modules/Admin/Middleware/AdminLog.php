<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AdminLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guard = 'admin';

        $resource = $next($request);

        $method = strtoupper($request->getMethod());

        if ($method != 'GET'){
            $ip_agent = get_client_info();
            $current_route=app('request')->route()[1];
            list($controller,$action)=explode('@', $current_route['uses']);
            \App\Common\Models\AdminLogs::insert([
                'request_data' => $action=='login' ? json_encode([]) : json_encode($request->all()),
                'user_id'     => !empty(auth($guard)->user()) ? auth($guard)->user()->user_id : 0,
                'created_ip'   => $ip_agent['ip'] ?? get_ip(),
                'browser_type' => $ip_agent['agent'] ?? $_SERVER['HTTP_USER_AGENT'],
                'created_at' => time(),
                'log_action'   => $current_route['uses'] ?? '',
                'log_method'   => $method,
                // 'log_duration' => microtime(true) - LARAVEL_START,
                'request_url'     => URL::full() ?? get_this_url(),
            ]);
        }

        return $resource;
    }
}
