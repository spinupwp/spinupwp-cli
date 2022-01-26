<?php

namespace App\OptionsIO\Sites;

use App\Helpers\OptionsHelper;
use App\OptionsIO\HasChoices;
use App\OptionsIO\Option;

class PhpVersion extends Option
{
    use HasChoices;

    protected $default = '80';

    protected string $promptValue = 'PHP Version';

    public function getChoices(): array
    {
        return OptionsHelper::PHP_VERSIONS;
    }
}
