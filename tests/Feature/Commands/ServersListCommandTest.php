<?php

use DeliciousBrains\SpinupWp\SpinupWp;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

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

    $clientMock = Mockery::mock(Client::class);
    $clientMock->shouldReceive('request')->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => $response,
        ]))
    );

    $this->mock(SpinupWp::class, function ($mock) use ($clientMock) {
        $mock->shouldReceive('getClient')->once()->andReturn($clientMock);
    });

    //    $su = $this->app->makeWith(SpinupWp::class, ['apiKey' => '123', 'client' => $clientMock]);
    // \Log::debug('asdf', $su->servers->list()->toArray());
    $this->artisan('servers:list')->expectsOutput(json_encode($response));
});
