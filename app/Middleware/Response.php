<?php

namespace App\Middleware;

use Closure;
use Illuminate\Support\Facades\Lang;

//后置中间件
class Response {

    /**
     * 响应后置处理
     * @param type $request
     * @param Closure $next
     * @return type
     */
    public function handle($request, Closure $next) {
        //这里体现出了后置中间件
        $response = $next($request)->original;
        if (is_array($response) && isset($response['exception'])) {
            $ret = [
                'ret' => 500,
                'response_at' => date('Y-m-d H:i:s'),
                'code' => $response['exception']['code'],
                'message' => $response['exception']['msg'],
                'data' => [],
            ];
        } else {
            $ret = [
                'ret' => 200,
                'response_at' => date('Y-m-d H:i:s'),
                'code' => '0000',
                'message' => Lang::get('common.response_message'),
                'data' => $response,
            ];
        }
        return response()->json($ret, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

}
