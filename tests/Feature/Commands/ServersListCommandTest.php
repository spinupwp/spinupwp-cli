<?php

use GuzzleHttp\Psr7\Response;

$response = [
    [
        'id'            => 1,
        'name'          => 'hellfish-media',
        'ip_address'    => '127.0.0.1',
        'provider_name' => 'DigitalOcean',
        'disk_space'    => [
            'total'      => 25210576000,
            'available'  => 17549436000,
            'used'       => 7661140000,
            'updated_at' => '2021-11-03T16:52:48.000000Z',
        ],
    ],
    [
        'id'            => 2,
        'name'          => 'staging.hellfish-media',
        'ip_address'    => '127.0.0.1',
        'provider_name' => 'DigitalOcean',
        'disk_space'    => [
            'total'      => 25210576000,
            'available'  => 17549436000,
            'used'       => 7661140000,
            'updated_at' => '2021-11-03T16:52:48.000000Z',
        ],
    ],
];
beforeEach(function () use ($response) {
    setTestConfigFile();
    $this->clientMock->shouldReceive('request')->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => $response,
        ]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

it('list command with no api token configured', function () use ($response) {
    $this->spinupwp->setApiKey('');
    $this->artisan('servers:list --profile=johndoe')
        ->assertExitCode(1);
    $this->spinupwp->setApiKey('123');
});

test('servers json list command', function () use ($response) {
    $this->artisan('servers:list')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('servers table list command', function () {
    $this->artisan('servers:list --format table')->expectsTable(
        ['ID', 'Name', 'IP Address'],
        [
            [
                '1',
                'hellfish-media',
                '127.0.0.1',
            ],
            [
                '2',
                'staging.hellfish-media',
                '127.0.0.1',
            ],
        ]
    );
});
