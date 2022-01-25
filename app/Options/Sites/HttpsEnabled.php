<?php

namespace App\Options\Sites;

use App\Options\Option;

class HttpsEnabled extends Option
{
    protected $default = true;

    protected string $promptType = 'confirm';

    protected string $promptValue = 'Enable HTTPS';
}
