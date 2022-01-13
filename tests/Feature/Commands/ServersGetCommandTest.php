<?php

use App\Helpers\Configuration;
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
    'ssh_port' => '22',
    'timezone' => 'UTC',
    'region'   => 'TOR1',
    'size'     => '1 GB / 1 vCPU',
    'database' => [
        'server' => 'mysql-8.0',
        'host'   => 'localhost',
        'port'   => 3306,
    ],
    'ssh_publickey'     => 'ssh-rsa AAAA....',
    'git_publickey'     => 'ssh-rsa AAAA....',
    'connection_status' => 'connected',
    'reboot_required'   => true,
    'upgrade_required'  => false,
    'install_notes'     => null,
    'created_at'        => '2021-01-01T12:00:00.000000Z',
    'status'            => 'provisioned',

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
    $this->artisan('servers:get 1 --format=table')->expectsTable([], [
        ['ID', '1'],
        ['Name', 'hellfish-media'],
        ['IP Address', '127.0.0.1'],
        ['Ubuntu', '20.04'],
        ['Database Server', 'mysql-8.0'],
    ]);
});

test('servers table get specified columns and asks to save it in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );
    $this->artisan('servers:get 1 --format=table --fields=id,name,ip_address')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'yes')
        ->expectsTable([], [
            ['ID', '1'],
            ['Name', 'hellfish-media'],
            ['IP Address', '127.0.0.1'],
        ]);
});

test('servers table get only columns saved in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $response]))
    );

    resolve(Configuration::class)->setCommandConfiguration('servers:get', 'fields', 'id,name');

    $this->artisan('servers:get 1 --format=table')
        ->expectsTable([], [
            ['ID', '1'],
            ['Name', 'hellfish-media'],
        ]);
});
