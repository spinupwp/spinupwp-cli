<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;

class HttpsEnabled extends Option
{
    protected $default = 1;

    protected $defaultWhenSkipped = 0;

    protected string $promptType = 'confirm';

    protected string $promptValue = 'Enable HTTPS';
}
