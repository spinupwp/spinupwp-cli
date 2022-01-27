<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;

class PageCacheEnabled extends Option
{
    protected $default = 1;

    protected $nonInteractiveDefault = 0;

    protected string $promptType = 'confirm';

    protected string $promptValue = 'Enable page cache';
}
