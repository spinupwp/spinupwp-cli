<?php

namespace App\Questions;

trait WithChoices
{
    abstract public function withChoices(array $choices): self;
}
