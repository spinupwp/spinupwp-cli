<?php

use GuzzleHttp\Psr7\Response;

$response = [

    'id'             => 1,
    'name'           => 'hellfish-media',
    'provider_name'  => 'DigitalOcean',
    'ubuntu_version' => '20.04',
    'ip_address'     => '127.0.0.1',
    'disk_space'     => [
        'total'      => 25210576000,
        'available'  => 17549436000,
        'used'       => 7661140000,
        'updated_at' => '2021-11-03T16:52:48.000000Z',
    ],
    'database' => [
        'server' => 'mysql-8.0',
    ],

];
beforeEach(function () use ($response) {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('servers json get command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );
    $this->artisan('servers:get 1')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('servers table get command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );
    $this->artisan('servers:get 1 --format=table')->expectsTable(
        ['ID', 'Name', 'IP Address', 'Ubuntu', 'Database'],
        [
            [
                '1',
                'hellfish-media',
                '127.0.0.1',
                '20.04',
                'mysql-8.0',
            ],
        ]
    );
});
