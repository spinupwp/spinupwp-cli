<?php

namespace App\Helpers;

class OptionsHelper
{
    // SpinupWP CLI only supports a subset of installation methods available via the REST API
    public const INSTALLATION_METHODS = [
        'wp'    => 'WordPress',
        'blank' => 'Don\'t Install Any Files',
    ];

    public const PHP_VERSIONS = ['8.0', '7.4'];

    public const REDIRECT_TYPES = [
        'permanent' => 'Permanent (301)',
        'redirect'  => 'Temporary (302)',
    ];
}
