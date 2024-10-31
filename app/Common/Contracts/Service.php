<?php

namespace App\Common\Contracts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Event
};
use Illuminate\Container\BoundMethod;

abstract class Service {

    public $request = [];
    public $response = [];
    public $middleware = [];
    public $beforeEvent = [];
    public $afterEvent = [];
    protected $lang = 'en';

    public function __construct() {
        //验证字段
        $this->lang = app('translator')->getLocale();
    }

    public function with($key, $value) {

        $this->request[$key] = $value;
        return $this;
    }

    public function pass($request) {
        $this->request = $request;
        return $this;
    }

    public function run($method = 'handle') {
        $this->validateService($method);
        $this->beforeEvent($this->request, $method);
        $this->response = $this->runPipeline($method);
        $this->afterEvent($this->response, $this->request, $method);
        return $this->response;
    }

    public function runTransaction($method = 'handle') {
        $this->validateService($method);
        $this->beforeEvent($this->request, $method);
        DB::beginTransaction();
        $this->response = $this->runPipeline($method);
        DB::commit();
        $this->afterEvent($this->response, $this->request, $method);
        return $this->response;
    }

    public function runPipeline($method) {
        return (new Pipeline(app()))
                        ->send($this->request)
                        ->through($this->getMiddleware($method))
                        ->then($this->process($this, $method));
    }

    //设置中间件
    public function setMiddleware($middleware = []) {
        $this->middleware = $middleware;
        return $this;
    }

    public function getMiddleware($method) {
        $baseMiddleware = $this->getScopeForMethod($this->middleware, $method);
        $configMiddleware = $this->getConfigMiddleware($method);
        $baseMiddleware = $baseMiddleware ? $baseMiddleware : [];
        $configMiddleware = $configMiddleware ? $configMiddleware : [];
        return array_merge($baseMiddleware, $configMiddleware);
    }

    public function process($class, $method) {
        return function ($request) use ($class, $method) {
            $method = get_called_class() . "@" . $method;
            return BoundMethod::call(app(), $method, [$request]);
            //  return call_user_func([$class, $method], $request);
        };
    }

    public function validateService($method) {
        if (!method_exists($this, $method))
            throw new \ReflectionException(get_called_class() . "->" . $method . "方法没找到");
        $rules = $this->getRules($this->request);
        $messages = $this->getMessages();
        if (isset($rules[$method]))
            app('validator')->validate($this->request, $rules[$method], $messages[$method] ?? [], []);
    }

    //设置事前时事件
    public function setBeforeEvent($event = []) {
        $this->beforeEvent = $event;
        return $this;
    }

    //设置事后事件
    public function setAfterEvent($event = []) {
        $this->beforeEvent = $event;
        return $this;
    }

    /**
     * 批量发送事前事件
     * @param $request
     * @param $method
     */
    public function beforeEvent($request, $method) {
        $beforeEvent = $this->getScopeForMethod($this->beforeEvent, $method);
        foreach ($beforeEvent as $event) {
            $this->eventFire($event, $request);
        }
    }

    /**
     * 批量发送事后事件
     * @param $request
     * @param $method
     */
    public function afterEvent($response, $request, $method) {
        $afterEvent = $this->getScopeForMethod($this->afterEvent, $method);
        foreach ($afterEvent as $event) {
            $this->eventFire($event, $response, $request);
        }
    }

    /**
     * 单个发送事件
     * @param $event
     * @param $request
     */
    public function eventFire($event, $response, $request) {
        Event::dispatch(new $event($response, $request));
    }

    /**
     * 根据数组获得方法相应的Event或者Middleware
     * @param $arrays
     * @param $method
     * @return array
     */
    public function getScopeForMethod($arrays, $method) {
        if ($method == 'handle') {
            return $arrays;
        }
        $result = [];
        foreach ($arrays as $name => $options) {
            if (isset($options['only']) && !in_array($method, (array) $options['only'])) {
                continue;
            }
            if (isset($options['except']) && in_array($method, (array) $options['except'])) {
                continue;
            }
            $result[] = $name;
        }
        return $result;
    }

    public function getConfigMiddleware($method) {
        return [];
    }

    abstract public function getRules();

    abstract public function getMessages();
}
