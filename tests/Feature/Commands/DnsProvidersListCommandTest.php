<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list DNS providers', function () {
    $dnsProviders = [
        ['id' => 1, 'service' => 'cloudflare', 'name' => 'Cloudflare', 'created_at' => '2024-07-01T12:00:00.000000Z'],
    ];

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'dns-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson($dnsProviders))
    );

    $this->artisan('dns-providers:list')
        ->expectsOutput(json_encode($dnsProviders, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list DNS providers when there are none', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'dns-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([]))
    );

    $this->artisan('dns-providers:list')
        ->expectsOutput('No DNS providers found.')
        ->assertExitCode(0);
});

test('list DNS providers as a table', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'dns-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'service' => 'cloudflare', 'name' => 'Cloudflare', 'created_at' => '2024-07-01T12:00:00.000000Z'],
        ]))
    );

    $this->artisan('dns-providers:list --format=table')
        ->expectsTable(['ID', 'Name', 'Service', 'Created At'], [
            [1, 'Cloudflare', 'cloudflare', '2024-07-01T12:00:00.000000Z'],
        ])
        ->assertExitCode(0);
});
