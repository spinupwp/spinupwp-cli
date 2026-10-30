<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'https' => ['enabled' => true]]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('disable https', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/https', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:https:disable 1')
        ->expectsConfirmation('Are you sure you want to disable HTTPS for "hellfishmedia.com"?', 'yes')
        ->expectsOutput('==> HTTPS queued for disabling.')
        ->assertExitCode(0);
});

test('disable https does nothing when not confirmed', function () {
    $this->clientMock->shouldNotReceive('request')->with('DELETE', 'sites/1/https', []);

    $this->artisan('sites:https:disable 1')
        ->expectsConfirmation('Are you sure you want to disable HTTPS for "hellfishmedia.com"?', 'no')
        ->assertExitCode(0);
});

test('disable https with force option does not prompt', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/https', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:https:disable 1 --force')
        ->expectsOutput('==> HTTPS queued for disabling.')
        ->assertExitCode(0);
});
