<?php

namespace App\Options\Sites;

use App\Options\Option;

class DbName extends Option
{
    protected string $promptValue = 'Database name';

    public function getDefault()
    {
        return str_replace('.', '', $this->default);
    }
}
