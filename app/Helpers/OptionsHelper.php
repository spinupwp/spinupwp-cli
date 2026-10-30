<?php

namespace App\Helpers;

class OptionsHelper
{
    public const INSTALLATION_METHODS = [
        'wp'              => 'WordPress',
        'wp_subdirectory' => 'WordPress Multisite (subdirectories)',
        'wp_subdomain'    => 'WordPress Multisite (subdomains)',
        'git'             => 'Git Repository',
        'blank'           => 'Don\'t Install Any Files',
    ];

    public const WORDPRESS_INSTALLATION_METHODS = ['wp', 'wp_subdirectory', 'wp_subdomain'];

    public const PHP_VERSIONS = ['8.5', '8.4', '8.3', '8.2', '8.1', '8.0', '7.4'];

    public const DEFAULT_PHP_VERSION = '8.4';

    public const SERVER_PROVIDERS = [
        'digitalocean' => 'DigitalOcean',
        'hetzner'      => 'Hetzner',
        'linode'       => 'Linode',
        'vultr'        => 'Vultr',
    ];

    public const REDIRECT_TYPES = [
        'permanent' => 'Permanent (301)',
        'redirect'  => 'Temporary (302)',
    ];
}
