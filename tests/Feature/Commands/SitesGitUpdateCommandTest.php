<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'git' => ['enabled' => false]]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('update git settings', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/git', [
        'json' => [
            'branch'         => 'production',
            'push_to_deploy' => false,
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:git:update 1 --branch=production --push-to-deploy=false')
        ->expectsOutput('==> Git settings queued for update.')
        ->assertExitCode(0);
});

test('update git settings without server changes', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/git', [
        'json' => ['push_to_deploy' => true],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => null])));

    $this->artisan('sites:git:update 1 --push-to-deploy=yes')
        ->expectsOutput('==> Git settings updated.')
        ->assertExitCode(0);
});

test('update git settings requires an option', function () {
    $this->artisan('sites:git:update 1')
        ->expectsOutput("At least one option must be provided. Run 'sites:git:update --help' to see the available options.")
        ->assertExitCode(1);
});

test('update git settings rejects invalid booleans', function () {
    $this->artisan('sites:git:update 1 --push-to-deploy=maybe')
        ->expectsOutput('The --push-to-deploy option must be true or false.')
        ->assertExitCode(1);
});
