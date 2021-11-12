<?php

use GuzzleHttp\Psr7\Response;

$response = [
    [
        'id'         => 1,
        'name'       => 'hellfish-media',
        'ip_address' => '127.0.0.1',
    ],
    [
        'id'         => 2,
        'name'       => 'staging.hellfish-media',
        'ip_address' => '127.0.0.1',
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
    $this->artisan('servers:list --profile=johndoe')
        ->assertExitCode(1);
});

test('servers json list command', function () use ($response) {
    $this->artisan('servers:list')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('servers table list command', function () use ($response) {
    $this->artisan('servers:list --format table')->expectsTable(array_keys($response[0]), [
        array_values($response[0]),
        array_values($response[1]),
    ]);
});
