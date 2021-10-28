<?php

use App\Helpers\Configuration;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

$response = [
    ['name' => 'hellfish-media'],
    ['name' => 'staging.hellfish-media'],
];

beforeEach(function () use ($response) {
    setTestConfigFile();

    $clientMock = Mockery::mock(Client::class);
    $clientMock->shouldReceive('request')->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => $response,
        ]))
    );

    Configuration::setCustomHttpClient('default', $clientMock);
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list command with no api token configured', function () use ($response) {
    $this->artisan('servers:list --profile=johndoe')
        ->assertExitCode(1);
});

test('servers json list command', function () use ($response) {
    $this->artisan('servers:list')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('servers table list command', function () use ($response) {
    $this->artisan('servers:list --format=table')->expectsTable([
        'name',
    ], [
        ['hellfish-media'],
        ['staging.hellfish-media'],
    ]);
});
