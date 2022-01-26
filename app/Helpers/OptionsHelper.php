<?php

namespace App\Helpers;

class OptionsHelper
{
    // SpinupWP CLI only supports a subset of installation methods available via the REST API
    const INSTALLATION_METHODS = ['wp', 'blank'];

    const PHP_VERSIONS = [
        '80' => '8.0',
        '74' => '7.4',
    ];
}
