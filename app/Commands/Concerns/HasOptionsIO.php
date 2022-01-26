<?php

namespace App\Commands\Concerns;

trait HasOptionsIO
{
    protected array $availableOptionIO = [];

    /**
     * Initializes an options object, outputs prompt for and return user input.
     *
     * @param bool|string|array $defaultOverride
     */
    protected function resolveOptionIO(string $optionClass, $defaultOverride = null): ?string
    {
        $optionClass = resolve($optionClass);

        if (!is_null($defaultOverride)) {
            $optionClass->default = $defaultOverride;
        }

        if (in_array('App\Options\HasChoices', class_uses($optionClass))) {
            return $this->{$optionClass->promptType}($optionClass->promptValue, $optionClass->choices, $optionClass->default);
        }
        return $this->{$optionClass->promptType}($optionClass->promptValue, $optionClass->default);
    }

    public function getAvailableOptionsIO(): array
    {
        return $this->availableOptionIO;
    }
}
