<?php

use GuzzleHttp\Psr7\Response;

$response = [
    'id'                 => 0,
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

// test('sites table get command', function () use ($response) {
//     $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
//         new Response(200, [], json_encode(['data' => $response]))
//     );
//     $this->artisan('sites:get 1 --format=table')->expectsTable([], [
//         ['ID', '1'],
//         ['Name', 'hellfish-media'],
//         ['Provider Name', 'DigitalOcean'],
//         ['IP Address', '127.0.0.1'],
//         ['SSH Port', '22'],
//         ['Ubuntu', '20.04'],
//         ['Timezone', 'UTC'],
//         ['Region', 'TOR1'],
//         ['Size', '1 GB / 1 vCPU'],
//         ['Disk Space', '7.7 GB of 25 GB used'],
//         ['Database Server', 'mysql-8.0'],
//         ['Database Host', 'localhost'],
//         ['Database Port', '3306'],
//         ['SSH Public Key', 'ssh-rsa AAAA....'],
//         ['Git Public Key', 'ssh-rsa AAAA....'],
//         ['Connection Status', 'Connected'],
//         ['Reboot Required', 'Yes'],
//         ['Upgrade Required', 'No'],
//         ['Install Notes', ''],
//         ['Created At', '2021-01-01T12:00:00.000000Z'],
//         ['Status', 'Provisioned'],
//     ]);
// });
