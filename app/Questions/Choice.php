<?php

namespace App\Questions;

class Choice extends Question
{
    use WithChoices;

    /** @var array<mixed> */
    private array $choices = [];

    /**
     * @param array<mixed> $choices
     * @return static
     */
    public function withChoices(array $choices): self
    {
        $this->choices = $choices;
        return $this;
    }

    /** @return string|array */
    public function question()
    {
        return $this->command->choice($this->prompt, $this->choices, $this->default);
    }
}
