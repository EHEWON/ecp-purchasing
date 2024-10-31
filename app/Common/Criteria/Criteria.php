<?php

namespace App\Common\Criteria;

use App\Common\Criteria\Concerns\{
    BuildsQueries,
    EloquentBuildsQueries,
    QueriesRelationships,
    SoftDeletingScope
};
use Closure;
use BadMethodCallException;
use Illuminate\Support\Str;

class Criteria {

    use BuildsQueries,
        QueriesRelationships,
        EloquentBuildsQueries,
        SoftDeletingScope;

    /**
     * $methods.
     *
     * @var \App\Common\Criteria\Method[]
     */
    protected $methods = [];

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters) {
        if (Str::startsWith($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        $className = static::class;

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }

    /**
     * create.
     *
     * @return static
     */
    public static function create() {
        return new static();
    }

    /**
     * alias raw.
     *
     * @param mixed $value
     * @return Expression
     */
    public static function expr($value) {
        return static::raw($value);
    }

    /**
     * @param mixed $value
     * @return \Expression
     */
    public static function raw($value) {
        return new Expression($value);
    }

    /**
     * each.
     *
     * @param  Closure $callback
     * @return void
     */
    public function each(Closure $callback) {
        foreach ($this->methods as $method) {
            $callback($method);
        }
    }

    /**
     * toArray.
     *
     * @return array
     */
    public function toArray() {
        return array_map(function ($method) {
            return [
                'method' => $method->name,
                'parameters' => $method->parameters,
            ];
        }, $this->methods);
    }

}
