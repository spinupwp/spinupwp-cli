<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list storage providers', function () {
    $storageProviders = [
        ['id' => 1, 'service' => 'aws-s3', 'name' => 'Production backups', 'created_at' => '2024-07-01T12:00:00.000000Z'],
    ];

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'storage-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson($storageProviders))
    );

    $this->artisan('storage-providers:list')
        ->expectsOutput(json_encode($storageProviders, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list storage providers when there are none', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'storage-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([]))
    );

    $this->artisan('storage-providers:list')
        ->expectsOutput('No storage providers found.')
        ->assertExitCode(0);
});

test('list storage providers as a table', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'storage-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'service' => 'aws-s3', 'name' => 'Production backups', 'created_at' => '2024-07-01T12:00:00.000000Z'],
        ]))
    );

    $this->artisan('storage-providers:list --format=table')
        ->expectsTable(['ID', 'Name', 'Service', 'Created At'], [
            [1, 'Production backups', 'aws-s3', '2024-07-01T12:00:00.000000Z'],
        ])
        ->assertExitCode(0);
});
