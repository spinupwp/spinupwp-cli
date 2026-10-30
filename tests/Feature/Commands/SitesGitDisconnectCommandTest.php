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

test('disconnect git', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/git', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:git:disconnect 1')
        ->expectsConfirmation('Are you sure you want to disconnect Git from "hellfishmedia.com"?', 'yes')
        ->expectsOutput('==> Git queued for disconnection.')
        ->assertExitCode(0);
});

test('disconnect git with force option', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/git', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:git:disconnect 1 --force')
        ->expectsOutput('==> Git queued for disconnection.')
        ->assertExitCode(0);
});

test('cancel disconnecting git', function () {
    $this->clientMock->shouldNotReceive('request')->with('DELETE', 'sites/1/git', []);

    $this->artisan('sites:git:disconnect 1')
        ->expectsConfirmation('Are you sure you want to disconnect Git from "hellfishmedia.com"?', 'no')
        ->doesntExpectOutput('==> Git queued for disconnection.')
        ->assertExitCode(0);
});
