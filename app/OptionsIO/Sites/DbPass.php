<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;
use Illuminate\Support\Str;

class DbPass extends Option
{
    protected string $promptValue = 'Database password';

    public function getDefault(): string
    {
        return Str::random(12);
    }
}
