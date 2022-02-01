<?php

namespace App\Commands\Concerns;

use App\Helpers\PromptHelper;

trait HasPrompts
{
    protected array $prompts = [];

    /**
     * @return mixed
     */
    protected function resolveAnswer(array $promptConfig, bool $nonInteractive = false)
    {
        $prompt  = PromptHelper::config($promptConfig);
        $default = PromptHelper::default($prompt);

        if ($nonInteractive) {
            return $promptConfig['nonInteractiveDefault'] ?? $default ?? null;
        }

        return $this->prompt($prompt, $default);
    }

    /**
     * @return mixed
     */
    protected function prompt(array $prompt, $default)
    {
        if ($prompt['type'] === 'choice') {
            return $this->{$prompt['type']}($prompt['prompt'], $prompt['choices'], $default);
        }
        return $this->{$prompt['type']}($prompt['prompt'], $default);
    }

    protected function promptForAnswers(bool $nonInteractive = false, string $type = 'option'): array
    {
        $userInput = [];

        foreach ($this->getPrompts() as $paramName => $config) {
            $userInput[$paramName] = $this->$type($paramName) ?? $this->resolveAnswer($config, $nonInteractive);
        }

        return $userInput;
    }

    protected function getPrompts(): array
    {
        return $this->prompts;
    }
}
