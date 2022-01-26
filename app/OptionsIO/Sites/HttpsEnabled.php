<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;

class HttpsEnabled extends Option
{
    protected $default = true;

    protected string $promptType = 'confirm';

    protected string $promptValue = 'Enable HTTPS';
}
