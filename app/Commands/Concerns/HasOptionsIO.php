<?php

namespace App\Commands\Concerns;

use App\OptionsIO\Option;

trait HasOptionsIO
{
    /**
     * @return mixed
     */
    protected function getOptionValue(Option $option, bool $nonInteractive = false)
    {
        if ($nonInteractive) {
            return $option->nonInteractiveDefault ?? $option->default;
        }

        return $this->promptForOption($option);
    }

    /**
     * @return mixed
     */
    protected function promptForOption(Option $option)
    {
        if (in_array('App\OptionsIO\HasChoices', class_uses($option))) {
            return $this->{$option->promptType}($option->promptValue, $option->choices, $option->default);
        }
        return $this->{$option->promptType}($option->promptValue, $option->default);
    }
}
