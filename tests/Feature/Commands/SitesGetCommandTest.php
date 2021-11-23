<?php

use GuzzleHttp\Psr7\Response;

$response = [
    'id'                 => 1,
    'server_id'          => 1,
    'domain'             => 'hellfish.media',
    'additional_domains' => [
        [
        'domain'   => 'www.hellfish.media',
        'redirect' => [
            'enabled' => true,
        ],
        'created_at' => '2019-08-24T14:15:22Z',
        ],
    ],
    'site_user'     => 'hellfishmedia',
    'php_version'   => '7.4',
    'public_folder' => '/',
    'is_wordpress'  => true,
    'page_cache'    => [
        'enabled' => true,
    ],
    'https' => [
        'enabled'          => true,
        'certificate_path' => '/etc/nginx/ssl/hellfish.media/certificate-bundle.crt',
        'private_key_path' => '/etc/nginx/ssl/hellfish.media/private-key.key',
    ],
    'nginx' => [
        'uploads_directory_protected'   => true,
        'xmlrpc_protected'              => true,
        'subdirectory_rewrite_in_place' => false,
    ],
    'database' => [
        'id'           => 1,
        'user_id'      => 1,
        'table_prefix' => 'wp_',
    ],
    'backups' => [
        'files'            => true,
        'database'         => true,
        'paths_to_exclude' => 'node_modules\\n/files/vendor',
        'retention_period' => 30,
        'next_run_time'    => '2021-01-01T12:00:00.000000Z',
        'storage_provider' => [
            'id'     => 1,
            'region' => 'nyc3',
            'bucket' => 'hellfish-media',
        ],
    ],
    'wp_core_update'    => true,
    'wp_theme_updates'  => 0,
    'wp_plugin_updates' => 3,
    'git'               => [
        'enabled'        => true,
        'repo'           => 'git@github.com:deliciousbrains/spinupwp-composer-site.git',
        'branch'         => 'main',
        'deploy_script'  => 'composer install --optimize-autoload --no-dev',
        'push_enabled'   => true,
        'deployment_url' => 'https://api.spinupwp.app/git/jeJLdKrl63/deploy',
    ],
    'basic_auth' => [
        'enabled'  => true,
        'username' => 'hellfish',
    ],
    'created_at' => '2021-01-01T12:00:00.000000Z',
    'status'     => 'deployed',

];
beforeEach(function () use ($response) {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('sites json get command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );
    $this->artisan('sites:get 1')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('sites table get command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );
    $this->artisan('sites:get 1 --format=table')->expectsTable([], [
        ['ID', '1'],
        ['Server ID', '1'],
        ['Domain', 'hellfish.media'],
        ['Additional Domains', 'www.hellfish.media'],
        ['Site User', 'hellfishmedia'],
        ['PHP Version', '7.4'],
        ['Public Folder', '/'],
        ['Is Wordpress', 'Yes'],
        ['Page Cache', 'Enabled'],
        ['HTTPS', 'Enabled'],
        ['Nginx', 'Uploads Directory Protected: Yes, XMLRPC Protected: Yes, Subdirectory Rewrite In Place: No'],
        ['Database', 'ID: 1, User ID: 1, Table Prefix: wp_'],
        ['Backups', 'Files: Yes, Database: Yes, Retention Period: 30, Next Run Time: 2021-01-01T12:00:00.000000Z'],
        ['WP Core Update', 'Yes'],
        ['WP Theme Updates', '0'],
        ['WP Plugin Updates', '3'],
        ['Git', 'Repo: git@github.com:deliciousbrains/spinupwp-composer-site.git, Branch: main, Deploy Script: composer install --optimize-autoload --no-dev'],
        ['Basic Auth', 'Enabled'],
        ['Created At', '2021-01-01T12:00:00.000000Z'],
        ['Status', 'Deployed'],
    ]);
});
