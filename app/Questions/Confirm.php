<?php

namespace App\Questions;

class Confirm extends Question
{
    public function question()
    {
        return $this->command->confirm($this->prompt, $this->default);
    }
}
