<?php

namespace APP\Middleware;

use Illuminate\Support\Facades\Request;

class CORSMiddleware {

    /**
     *
     *  *Request Header
     * Origin:表明发送请求或预请求的来源。
     * Access-Control-Request-Method:在发送预请求时带该请求头,表明实际的请求将使用的请求方式
     * Access-Control-Request-Headers:在发送预请求时带有该请求头,表明实际的请求将携带的请求头
     *
     * Response Header
     * Access-Control-Allow-Origin:指明哪些请求源被允许访问资源,值可以为 "*","null",或者单个源地址
     * Access-Control-Allow-Credentials:指明当请求中省略 creadentials 标识时响应是否暴露
     * 对于预请求来说，它表明实际的请求中可以包含用户凭证
     * Access-Control-Expose-Headers:指明哪些头信息可以安全的暴露给 CORS API 规范的 API
     * Access-Control-Max-Age : 指明预请求可以在预请求缓存中存放多久
     * Access-Control-Allow-Methods:对于预请求来说，哪些请求方式可以用于实际的请求
     * Access-Control-Allow-Headers:对于预请求来说，指明了哪些头信息可以用于实际的请求中
     * Origin : 指明预请求或者跨域请求的来源。
     * Access-Control-Request-Method:对于预请求来说,指明哪些预请求中的请求方式可以被用在实际的请求中
     * Access-Control-Request-Headers:指明预请求中的哪些头信息可以用于实际的请求中。
     *

     * 对于跨域访问并需要伴随认证信息的请求,需要在XMLHttpRequest实例中指定withCredentials为true
     * 这个中间件你可以根据自己的需求进行构建
     * 如果需要在请求中伴随认证信息（包含 cookie,session）需要指定 Access-Control-Allow-Credentials为true,
     * 因为对于预请求来说如果你未指定该响应头,那么浏览器会直接忽略该响应
     * 在响应中指定 Access-Control-Allow-Credentials为true时,Access-Control-Allow-Origin不能指定为 *
     * 后置中间件只有在正常响应时才会被追加响应头，而如果出现异常，这时响应是不会经过中间件的。
     *
     */
    public function handle($request, \Closure $next) {
        header('Access-Control-Allow-Origin:' . Request::header('Origin'));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers:Content-Type, Accept, Authorization, X-Requested-With,token,language');
        header('Access-Control-Allow-Methods:POST, GET, OPTIONS, PUT, DELETE, PATCH');
        return $next($request);
    }

}
