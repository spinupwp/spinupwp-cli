<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class OptionsHelper
{
    // SpinupWP CLI only supports a subset of installation methods available via the REST API
    public const INSTALLATION_METHODS = [
        'wp'    => 'WordPress',
        'blank' => 'Don\'t Install Any Files',
    ];

    public const PHP_VERSIONS = ['8.0', '7.4'];

    public static function getDomainSlug(string $domain, int $maxLength = 32): string
    {
        $parsedDomain = parse_url($domain);

        $domain = data_get($parsedDomain, 'host', data_get($parsedDomain, 'path'));

        $names = explode('.', $domain);

        $name = array_shift($names);

        if (strtolower($name) === 'www') {
            $name = array_shift($names);
        }

        $name = str_replace(['-', '_'], '', $name);

        return substr(Str::slug($name), 0, $maxLength);
    }
}
