<?php

namespace App\Options\Sites;

use App\Options\Option;

class DbUser extends Option
{
    protected string $promptValue = 'Database username';

    public function getDefault()
    {
        return str_replace('.', '', $this->default);
    }
}
