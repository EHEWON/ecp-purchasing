<?php

namespace App\Common\Contracts;

abstract class Module {

    public $listen;
    public $subscribe;

    public function __construct() {
        $this->registerEvent();
    }

    public function service($service) {
        $class = get_called_class();
        $namespance = substr($class, 0, strripos($class, "\\"));
        $service = $namespance . "\\Service\\" . $service;
        return app()->make($service);
    }

    public function registerEvent() {
        $events = app('events');
        $listen = $this->getListen();
        $this->listen = is_array($listen) ? $listen : [];
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
        $subscribe = $this->getSubscribe();
        $this->subscribe = is_array($subscribe) ? $subscribe : [];
        foreach ($this->subscribe as $subscriber) {
            $events->subscribe($subscriber);
        }
    }

    abstract public function getListen();

    abstract public function getSubscribe();
}
