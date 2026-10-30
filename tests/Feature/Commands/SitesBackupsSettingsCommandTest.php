<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->site = ['id' => 1, 'domain' => 'hellfishmedia.com'];

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $this->site]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('update backup settings', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/backup-settings', [
        'json' => [
            'storage_provider_id'     => 1,
            'storage_provider_bucket' => 'turnipjuice-media',
            'storage_provider_region' => 'nyc3',
            'paths_to_exclude'        => "node_modules\n/files/vendor",
            'database'                => 2,
        ],
    ])->andReturn(new Response(200, [], json_encode(['data' => array_merge($this->site, [
        'backups' => [
            'paths_to_exclude' => "node_modules\n/files/vendor",
            'storage_provider' => ['id' => 1, 'region' => 'nyc3', 'bucket' => 'turnipjuice-media'],
        ],
    ])])));

    $this->artisan('sites:backups:settings 1 --storage-provider-id=1 --bucket=turnipjuice-media --region=nyc3 --database=2 --exclude-path=node_modules --exclude-path=/files/vendor')
        ->expectsOutput('==> Backup settings updated.')
        ->expectsOutputToContain('node_modules, /files/vendor')
        ->assertExitCode(0);
});

test('clear the backup database', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/backup-settings', [
        'json' => ['database' => null],
    ])->andReturn(new Response(200, [], json_encode(['data' => $this->site])));

    $this->artisan('sites:backups:settings 1 --database=none')
        ->expectsOutput('==> Backup settings updated.')
        ->assertExitCode(0);
});

test('update backup settings shows the saved paths when the site has no backup schedule', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/backup-settings', [
        'json' => ['paths_to_exclude' => "node_modules\n/files/vendor"],
    ])->andReturn(new Response(200, [], json_encode(['data' => array_merge($this->site, [
        'backups' => [
            'paths_to_exclude' => null,
            'storage_provider' => ['id' => 1, 'region' => 'nyc3', 'bucket' => 'turnipjuice-media'],
        ],
    ])])));

    $this->artisan('sites:backups:settings 1 --exclude-path=node_modules --exclude-path=/files/vendor')
        ->expectsOutput('==> Backup settings updated.')
        ->expectsOutputToContain('node_modules, /files/vendor')
        ->assertExitCode(0);
});
