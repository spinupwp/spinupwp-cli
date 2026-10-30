<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->sizes = [
        ['slug' => 's-1vcpu-2gb', 'type' => 'basic', 'memory' => 2048, 'vcpus' => 1, 'disk' => 50, 'transfer' => 2, 'priceMonthly' => 12, 'backupPriceMonthly' => 2.4, 'available' => true, 'warning' => ''],
        ['slug' => 's-1vcpu-512mb', 'type' => 'basic', 'memory' => 512, 'vcpus' => 1, 'disk' => 10, 'transfer' => 0.5, 'priceMonthly' => 4, 'backupPriceMonthly' => 0.8, 'available' => false, 'warning' => 'We strongly recommend at least 2 GB of memory.'],
    ];

    $this->clientMock->shouldReceive('request')->with('GET', 'providers/digitalocean/metadata', [])->andReturn(
        new Response(200, [], json_encode([
            'regions' => [
                'Americas' => [
                    ['slug' => 'nyc3', 'name' => 'New York 3', 'available' => true, 'sizes' => ['s-1vcpu-2gb']],
                ],
            ],
            'sizes' => $this->sizes,
        ]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list provider sizes as a table', function () {
    $this->artisan('providers:sizes digitalocean --format=table')
        ->expectsTable(['Slug', 'vCPUs', 'Memory', 'Disk', 'Transfer', 'Price', 'Available'], [
            ['s-1vcpu-2gb', 1, '2 GB', '50 GB', '2 TB', '$12.00/month', 'Yes'],
            ['s-1vcpu-512mb', 1, '512 MB', '10 GB', '0.5 TB', '$4.00/month', 'No'],
        ])
        ->assertExitCode(0);
});

test('list provider sizes for a region as json', function () {
    $this->artisan('providers:sizes digitalocean --region=nyc3')
        ->expectsOutput(json_encode([$this->sizes[0]], JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list provider sizes for an unknown region', function () {
    $this->artisan('providers:sizes digitalocean --region=mars1')
        ->expectsOutput('No region "mars1" found. Run providers:regions to see the available regions.')
        ->assertExitCode(1);
});
