<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;

class DbUser extends Option
{
    protected string $promptValue = 'Database username';

    public function getDefault()
    {
        return str_replace('.', '', $this->default);
    }
}
