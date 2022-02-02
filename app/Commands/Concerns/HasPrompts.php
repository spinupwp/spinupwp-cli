<?php

namespace App\Commands\Concerns;

trait HasPrompts
{
    protected array $questions = [];

    /**
     * @param array $prompt
     * @return mixed
     */
    protected function doPrompt(array $prompt)
    {
        if ($prompt['type'] === 'choice') {
            return $this->{$prompt['type']}($prompt['prompt'], $prompt['choices'], $prompt['default']);
        }
        return $this->{$prompt['type']}($prompt['prompt'], $prompt['default']);
    }

    protected function doPrompts(array $prompts, bool $nonInteractive = false, string $type = 'option'): array
    {
        $userInput = [];

        foreach ($prompts as $paramName => $prompt) {
            $cliInput = $this->$type($paramName);

            if (!empty($cliInput) || $nonInteractive) {
                $userInput[$paramName] = $cliInput ?? $prompt['default'];
            } else {
                $userInput[$paramName] = $this->doPrompt($prompt);
            }
        }

        return $userInput;
    }
}
