<?php

use DeliciousBrains\SpinupWp\Endpoints\Server;
use GuzzleHttp\Client;

beforeEach(function () {
    setConfigPath();
    deleteTestConfigFile();
});

test('list command with no api token configured', function () {
    $this->artisan('servers:list --profile=johndoe')
        ->assertExitCode(1);
});

test('list command', function () {
    $response = [
        ['name' => 'hellfish-media'],
        ['name' => 'staging.hellfish-meida'],
    ];

    $this->app->instance(Server::class, function ($mock) use ($response) {
        $mock->shouldReceive('list')->andReturn($response);
    });

    $this->app->instance(Client::class, $clientMock);

    $this->artisan('servers:list')->expectsOutput(json_encode($response));
});
