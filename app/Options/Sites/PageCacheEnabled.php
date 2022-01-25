<?php

namespace App\Options\Sites;

use App\Options\Option;

class PageCacheEnabled extends Option
{
    protected $default = true;

    protected string $promptType = 'confirm';

    protected string $promptValue = 'Enable page cache';
}
