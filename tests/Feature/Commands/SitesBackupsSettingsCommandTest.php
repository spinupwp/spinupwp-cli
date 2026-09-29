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
    ])->andReturn(new Response(200, [], json_encode(['data' => $this->site])));

    $this->artisan('sites:backups:settings 1 --storage-provider-id=1 --bucket=turnipjuice-media --region=nyc3 --database=2 --exclude-path=node_modules --exclude-path=/files/vendor')
        ->expectsOutput('==> Backup settings updated.')
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
