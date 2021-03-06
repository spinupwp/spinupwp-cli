<?php

namespace App\Questions;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

abstract class Question
{
    use InteractsWithIO;

    protected Command $command;

    /** @var mixed */
    protected $default = null;

    public string $flag = '';

    public string $key = '';

    protected bool $nonInteractive = false;

    protected string $prompt = '';

    public bool $skip = false;

    final public function __construct(string $prompt)
    {
        $this->prompt = $prompt;
        $this->flag   = Str::kebab($prompt);
    }

    /** @return static */
    public static function make(string $prompt): self
    {
        return new static($prompt);
    }

    /**
     * @param mixed $default
     * @return static
     */
    public function withDefault($default): self
    {
        $this->default = $default;

        return $this;
    }

    /** @return static */
    public function withFlag(string $flag): self
    {
        $this->flag = $flag;
        return $this;
    }

    public function withKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /** @return static */
    public function nonInteractive(bool $nonInteractive = true): self
    {
        $this->nonInteractive = $nonInteractive;

        return $this;
    }

    /** @return mixed */
    public function resolveAnswer(Command $command)
    {
        $this->command = $command;

        $flagInput = !empty($this->flag) ? $this->command->option($this->flag) : null;

        if ($flagInput || $this->nonInteractive) {
            return $flagInput ?? $this->default ?? null;
        }

        return $this->question();
    }

    public function unless(callable $callback): self
    {
        $this->skip = (bool) $callback();

        return $this;
    }

    /** @return mixed */
    abstract protected function question();
}
