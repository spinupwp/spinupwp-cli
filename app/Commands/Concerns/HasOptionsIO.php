<?php

namespace App\Commands\Concerns;

use App\OptionsIO\Option;

trait HasOptionsIO
{
    /**
     * Initializes an options object, outputs prompt for and return user input.
     *
     * @param bool|string|array $defaultOverride
     */
    protected function resolveOptionIO(string $optionClass, $defaultOverride = null, bool $nonInteractive = false): ?string
    {
        $optionClass = resolve($optionClass);

        if (!is_null($defaultOverride)) {
            $optionClass->default = $defaultOverride;
        }

        if ($nonInteractive) {
            return $optionClass->nonInteractiveDefault ?? $optionClass->default;
        }

        return $this->promptForOption($optionClass);
    }

    /**
     * @return mixed
     */
    protected function promptForOption(Option $optionClass)
    {
        if (in_array('App\OptionsIO\HasChoices', class_uses($optionClass))) {
            return $this->{$optionClass->promptType}($optionClass->promptValue, $optionClass->choices, $optionClass->default);
        }
        return $this->{$optionClass->promptType}($optionClass->promptValue, $optionClass->default);
    }
}
