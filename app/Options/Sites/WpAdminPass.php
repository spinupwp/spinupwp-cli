<?php

namespace App\Options\Sites;

use App\Options\Option;
use Illuminate\Support\Str;

class WpAdminPass extends Option
{
    protected string $promptValue = 'WordPress admin password';

    public function getDefault(): string
    {
        return Str::random(12);
    }
}
