<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'providers/digitalocean/metadata', [])->andReturn(
        new Response(200, [], json_encode([
            'regions' => [
                'Americas' => [
                    ['slug' => 'nyc3', 'name' => 'New York 3', 'available' => true, 'sizes' => ['s-1vcpu-2gb']],
                ],
                'Europe' => [
                    ['slug' => 'ams3', 'name' => 'Amsterdam 3', 'available' => false, 'sizes' => []],
                ],
            ],
            'sizes' => [],
        ]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list provider regions as a table', function () {
    $this->artisan('providers:regions digitalocean --format=table')
        ->expectsTable(['Slug', 'Name', 'Continent', 'Available'], [
            ['nyc3', 'New York 3', 'Americas', 'Yes'],
            ['ams3', 'Amsterdam 3', 'Europe', 'No'],
        ])
        ->assertExitCode(0);
});

test('list provider regions as json', function () {
    $this->artisan('providers:regions digitalocean')
        ->expectsOutput(json_encode([
            ['slug' => 'nyc3', 'name' => 'New York 3', 'continent' => 'Americas', 'available' => true],
            ['slug' => 'ams3', 'name' => 'Amsterdam 3', 'continent' => 'Europe', 'available' => false],
        ], JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list provider regions asks for the provider', function () {
    $this->artisan('providers:regions --format=table')
        ->expectsChoice('Which server provider', 'digitalocean', ['digitalocean' => 'DigitalOcean', 'hetzner' => 'Hetzner', 'linode' => 'Linode', 'vultr' => 'Vultr'])
        ->expectsTable(['Slug', 'Name', 'Continent', 'Available'], [
            ['nyc3', 'New York 3', 'Americas', 'Yes'],
            ['ams3', 'Amsterdam 3', 'Europe', 'No'],
        ])
        ->assertExitCode(0);
});

test('list provider regions rejects an unknown provider', function () {
    $this->artisan('providers:regions aws')
        ->expectsOutput('The provider must be one of: digitalocean, hetzner, linode, vultr.')
        ->assertExitCode(1);
});
