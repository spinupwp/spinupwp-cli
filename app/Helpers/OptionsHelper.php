<?php

namespace App\Helpers;

class OptionsHelper
{
    // SpinupWP CLI only supports a subset of installation methods available via the REST API
    public const INSTALLATION_METHODS = ['wp', 'blank'];

    public const PHP_VERSIONS = [
        '80' => '8.0',
        '74' => '7.4',
    ];
}
