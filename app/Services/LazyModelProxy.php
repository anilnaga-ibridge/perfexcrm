<?php

namespace App\Services;

class LazyModelProxy
{
    protected $loader;
    protected $model;
    protected $name;
    protected $realInstance = null;

    public function __construct($loader, string $model, string $name)
    {
        $this->loader = $loader;
        $this->model = $model;
        $this->name = $name;
    }

    /**
     * Resolve and return the real model instance.
     */
    public function getRealInstance()
    {
        if ($this->realInstance === null) {
            $this->realInstance = $this->loader->realizeModel($this->model, $this->name);
        }
        return $this->realInstance;
    }

    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->getRealInstance(), $method], $args);
    }

    public function __get(string $property)
    {
        return $this->getRealInstance()->$property;
    }

    public function __set(string $property, $value): void
    {
        $this->getRealInstance()->$property = $value;
    }

    public function __isset(string $property): bool
    {
        return isset($this->getRealInstance()->$property);
    }
}
