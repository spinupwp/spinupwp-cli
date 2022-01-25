<?php

namespace App\Options;

trait HasChoices
{
    public function getChoices():array
    {
        return $this->choices ?? [];
    }

    public function getPromptType(): string
    {
        return 'askToSelect';
    }
}
