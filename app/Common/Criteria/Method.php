<?php

namespace App\Common\Criteria;

class Method {

    /**
     * $name.
     *
     * @var string
     */
    public $name;

    /**
     * $parameters.
     *
     * @var mixed
     */
    public $parameters;

    /**
     * @param string $name
     * @param mixed  $parameters
     */
    public function __construct($name, $parameters = []) {
        $this->name = $name;
        $this->parameters = $parameters;
    }

}
