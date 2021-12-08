<?php

use App\Helpers\Configuration;
use GuzzleHttp\Psr7\Response;

$response = [
    [
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
    ],
    [
        'id'             => 2,
        'name'           => 'staging.hellfish-media',
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
    ],
];
beforeEach(function () use ($response) {
    setTestConfigFile();
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
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );
    $this->artisan('servers:list')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('servers table list command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );
    $this->artisan('servers:list --format table')->expectsTable(
        ['ID', 'Name', 'IP Address', 'Ubuntu', 'Database Server'],
        [
            [
                '1',
                'hellfish-media',
                '127.0.0.1',
                '20.04',
                'mysql-8.0',
            ],
            [
                '2',
                'staging.hellfish-media',
                '127.0.0.1',
                '20.04',
                'mysql-8.0',
            ],
        ]
    );
});

test('servers table list with specified columns command and asks to save it in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );
    $this->artisan('servers:list --format=table --fields=id,name,ip_address')->expectsConfirmation('Do you want to save the specified fields as default for this command?', 'yes')->expectsTable(
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

    $this->assertEquals('id,name,ip_address', resolve(Configuration::class)->getCommandConfiguration('servers:list')['fields']);
});

test('servers table list only columns saved in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );

    resolve(Configuration::class)->setCommandConfiguration('servers:list', 'fields', 'id,name');

    $this->artisan('servers:list --format=table')->expectsTable(
        ['ID', 'Name'],
        [
            [
                '1',
                'hellfish-media',
            ],
            [
                '2',
                'staging.hellfish-media',
            ],
        ]
    );
});

test('empty servers list', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], listResponseJson([]))
    );
    $this->artisan('servers:list')->expectsOutput('No servers found.');
});
