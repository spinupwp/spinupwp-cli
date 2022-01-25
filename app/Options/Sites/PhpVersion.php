<?php

namespace App\Options\Sites;

use App\Options\HasChoices;
use App\Options\Option;

class PhpVersion extends Option
{
    use HasChoices;

    protected array $choices = [
        '8'  => '8.0',
        '74' => '7.4',
    ];

    protected $default = '8';

    protected string $promptValue = 'PHP Version';
}
