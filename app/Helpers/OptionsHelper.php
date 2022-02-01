<?php

namespace App\Helpers;

class OptionsHelper
{
    // SpinupWP CLI only supports a subset of installation methods available via the REST API
    public const INSTALLATION_METHODS = ['wp', 'blank'];

    public const PHP_VERSIONS = [
        '8.0' => '8.0',
        '7.4' => '7.4',
    ];
}
