<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;

class PageCacheEnabled extends Option
{
    protected $default = true;

    protected string $promptType = 'confirm';

    protected string $promptValue = 'Enable page cache';
}
