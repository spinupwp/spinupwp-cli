<?php

namespace App\Questions;

class Ask extends Question
{
    protected function question()
    {
        return $this->command->ask($this->prompt, $this->default);
    }
}
