<?php

namespace App\Options;

abstract class Option
{
    /**
     * @var string|array|null
     */
    protected $default = null;

    protected string $promptType = 'ask';

    protected string $promptValue = '';

    public function __get(string $name)
    {
        if (method_exists($this, $method = 'get' . ucfirst($name))) {
            return $this->$method();
        }
        return $this->{$name};
    }

    public function __set(string $name, $value): void
    {
        $this->{$name} = $value;
    }
}
