<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIpBlacklist
{

    protected $guard;
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $client_ip = $this->request->getClientIp();
        if (!$client_ip){
            $client_ip = get_ip();
        }

        // 获取黑名单组
        $ip_blacklists = config('admin.ip_blacklists');
        if ($ip_blacklists){
            $ip_blacklists_array = explode(',', $ip_blacklists);
            // 键值翻转，检测是否存在数组key
            $ip_blacklists_array = array_flip($ip_blacklists_array);
            if (isset($ip_blacklists_array[$client_ip])){
                $msg = '您的IP段在系统黑名单中，禁止访问！';
                abort(403, $msg);
            }
        }


        return $next($request);
    }
}
