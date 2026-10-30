<?php

namespace App\Questions;

class Ask extends Question
{
    protected bool $hidden = false;

    /**
     * Hide the answer as it is typed, for passwords and other sensitive values.
     *
     * @return static
     */
    public function hidden(): self
    {
        $this->hidden = true;

        return $this;
    }

    protected function question()
    {
        if ($this->hidden) {
            return $this->command->secret($this->prompt);
        }

        return $this->command->ask($this->prompt, $this->default);
    }
}
