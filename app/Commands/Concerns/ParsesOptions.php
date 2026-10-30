<?php

namespace App\Commands\Concerns;

use Exception;

trait ParsesOptions
{
    protected function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) ? $value : null;
    }

    protected function intOption(string $name): ?int
    {
        $value = $this->stringOption($name);

        if (is_null($value)) {
            return null;
        }

        if (!is_numeric($value) || (string) (int) $value !== $value) {
            throw new Exception("The --{$name} option must be an integer.");
        }

        return (int) $value;
    }

    protected function boolOption(string $name): ?bool
    {
        $value = $this->stringOption($name);

        if (is_null($value)) {
            return null;
        }

        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (is_null($bool)) {
            throw new Exception("The --{$name} option must be true or false.");
        }

        return $bool;
    }

    /**
     * Parse a comma-separated list of integers. Passing "none" returns an empty list.
     */
    protected function intListOption(string $name): ?array
    {
        $value = $this->stringOption($name);

        if (is_null($value)) {
            return null;
        }

        if ($value === 'none') {
            return [];
        }

        return array_map(function ($item) use ($name) {
            $item = trim($item);

            if (!is_numeric($item) || (string) (int) $item !== $item) {
                throw new Exception("The --{$name} option must be a comma-separated list of integers.");
            }

            return (int) $item;
        }, explode(',', $value));
    }

    /**
     * Join the values of a repeatable option into a newline-separated string.
     */
    protected function multilineOption(string $name): ?string
    {
        $values = $this->option($name);

        if (!is_array($values) || empty($values)) {
            return null;
        }

        return implode("\n", array_filter($values, fn ($value) => is_string($value) && $value !== ''));
    }

    protected function withoutNulls(array $payload): array
    {
        return array_filter($payload, fn ($value) => !is_null($value));
    }

    protected function ensureOptionsProvided(array $payload): void
    {
        if (empty($payload)) {
            throw new Exception("At least one option must be provided. Run '{$this->getName()} --help' to see the available options.");
        }
    }
}
