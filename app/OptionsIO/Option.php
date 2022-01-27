<?php

namespace App\OptionsIO;

abstract class Option
{
    /**
     * @var mixed default value when prompt
     */
    protected $default = null;

    /**
     * @var mixed default value when prompt is not displayed
     */
    protected $nonInteractiveDefault = null;

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
