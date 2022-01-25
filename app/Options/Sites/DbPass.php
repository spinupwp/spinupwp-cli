<?php

namespace App\Options\Sites;

use App\Options\Option;
use Illuminate\Support\Str;

class DbPass extends Option
{
    protected string $promptValue = 'Database password';

    public function getDefault(): string
    {
        return Str::random(12);
    }
}
