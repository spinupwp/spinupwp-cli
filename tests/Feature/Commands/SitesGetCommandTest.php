<?php

use App\Repositories\ConfigRepository as Configuration;
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
        'repo'           => 'git@github.com:spinupwp/spinupwp-composer-site.git',
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
        ['Site User', 'hellfishmedia'],
        ['PHP Version', '7.4'],
        ['Page Cache', 'Enabled'],
        ['HTTPS', 'Enabled'],
    ]);
});

test('site table get command specifying columns and asks to save it in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );
    $this->artisan('sites:get 1 --format=table --fields=domain,git,backups,status')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'yes')
        ->expectsTable([], [
            ['Domain', 'hellfish.media'],
            ['Git', 'Enabled'],
            ['Repository', 'git@github.com:spinupwp/spinupwp-composer-site.git'],
            ['Branch', 'main'],
            ['Deploy Script', 'composer install --optimize-autoload --no-dev'],
            ['Push-to-deploy', 'Enabled'],
            ['Deployment URL', 'https://api.spinupwp.app/git/jeJLdKrl63/deploy'],
            ['Scheduled Backups', 'Enabled'],
            ['File Backups', 'Enabled'],
            ['Database Backups', 'Enabled'],
            ['Backup Retention Period', '30 days'],
            ['Next Backup Time', '2021-01-01T12:00:00.000000Z'],
            ['Status', 'Deployed'],
        ]);
});

test('site table get only columns saved in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );

    resolve(Configuration::class)->setCommandConfiguration('sites:get', 'fields', 'id,domain');

    $this->artisan('sites:get 1 --format=table')
        ->expectsTable([], [
            ['ID', '1'],
            ['Domain', 'hellfish.media'],
        ]);

    $this->artisan('sites:get 1')->expectsOutput(json_encode([
        'id'     => 1,
        'domain' => 'hellfish.media',
    ], JSON_PRETTY_PRINT));
});

test('site table get command with wp cron fields', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'     => 1,
            'domain' => 'hellfish.media',
            'cron'   => ['enabled' => true, 'interval' => 15],
        ]]))
    );

    $this->artisan('sites:get 1 --format=table --fields=domain,cron')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'no')
        ->expectsTable([], [
            ['Domain', 'hellfish.media'],
            ['WP Cron', 'Enabled'],
            ['WP Cron Interval', '15 minutes'],
        ]);
});

test('site table get command with newer api fields', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'         => 1,
            'domain'     => 'hellfish.media',
            'user_auth'  => 'publickey',
            'page_cache' => ['enabled' => true, 'duration' => 1, 'duration_unit' => 'd'],
            'https'      => ['enabled' => true, 'certificate_expires' => '28 Jan 2027'],
            'backups'    => [
                'files'    => true,
                'database' => false,
                'schedule' => [
                    'daily'   => ['backup_database' => false, 'backup_files' => true, 'retention_period' => 7, 'time_of_day' => [3]],
                    'weekly'  => ['backup_database' => false, 'backup_files' => false, 'retention_period' => null, 'time_of_day' => null, 'day_of_week' => []],
                    'monthly' => ['backup_database' => false, 'backup_files' => true, 'retention_period' => 1, 'time_of_day' => 4, 'day_of_month' => [['week_of_month' => 'second', 'day_of_week' => 0]]],
                ],
            ],
            'subdomain'   => ['enabled' => true, 'url' => 'https://hellfish-media.spinupwp.site'],
            'disabled_at' => '2026-10-01T12:00:00.000000Z',
        ]]))
    );

    $this->artisan('sites:get 1 --format=table --fields=user_auth,page_cache,page_cache.duration,https,https.certificate_expires,backups.schedule,subdomain,subdomain.url,disabled_at')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'no')
        ->expectsTable([], [
            ['SSH Authentication', 'SSH keys'],
            ['Page Cache', 'Enabled'],
            ['Page Cache Duration', '1 day'],
            ['HTTPS', 'Enabled'],
            ['HTTPS Certificate Expires', '28 Jan 2027'],
            ['Daily Backups', '03:00, files, kept 7 days'],
            ['Weekly Backups', 'Disabled'],
            ['Monthly Backups', 'Second Sunday at 04:00, files, kept 1 day'],
            ['SpinupWP Subdomain', 'Enabled'],
            ['SpinupWP Subdomain URL', 'https://hellfish-media.spinupwp.site'],
            ['Disabled At', '2026-10-01T12:00:00.000000Z'],
        ]);
});

test('site table get command hides fields that do not apply', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'          => 1,
            'domain'      => 'hellfish.media',
            'page_cache'  => ['enabled' => false],
            'https'       => ['enabled' => false],
            'subdomain'   => ['enabled' => false],
            'disabled_at' => null,
        ]]))
    );

    $this->artisan('sites:get 1 --format=table --fields=domain,page_cache,https,subdomain,disabled_at')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'no')
        ->expectsTable([], [
            ['Domain', 'hellfish.media'],
            ['Page Cache', 'Disabled'],
            ['HTTPS', 'Disabled'],
            ['SpinupWP Subdomain', 'Disabled'],
        ]);
});

test('site json get command keeps the page cache and https keys when filtering fields', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'         => 1,
            'domain'     => 'hellfish.media',
            'page_cache' => ['enabled' => true, 'duration' => 2, 'duration_unit' => 'h'],
            'https'      => ['enabled' => true, 'certificate_expires' => '28 Jan 2027'],
        ]]))
    );

    $this->artisan('sites:get 1 --format=json --fields=domain,page_cache,https,page_cache.duration')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'no')
        ->expectsOutput(json_encode([
            'domain'              => 'hellfish.media',
            'page_cache'          => 'Enabled',
            'page_cache.duration' => '2 hours',
            'https'               => 'Enabled',
        ], JSON_PRETTY_PRINT));
});

test('site table get command warns when none of the requested fields apply', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'     => 1,
            'domain' => 'hellfish.media',
            'git'    => ['enabled' => false],
        ]]))
    );

    $this->artisan('sites:get 1 --format=table --fields=git.repo')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'no')
        ->expectsOutput('None of the requested fields apply to this resource.')
        ->assertExitCode(0);
});
