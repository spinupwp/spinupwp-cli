<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('correct file permissions for a site', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com']]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/file-permissions/correct', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:correct-permissions 1')
        ->expectsOutput('==> File permissions queued for correction.')
        ->assertExitCode(0);
});

test('correct file permissions asks which site', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com'],
            ['id' => 2, 'domain' => 'staging.hellfishmedia.com'],
        ]))
    );

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/2', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 2, 'domain' => 'staging.hellfishmedia.com']]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/2/file-permissions/correct', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 101]))
    );

    $this->artisan('sites:correct-permissions')
        ->expectsChoice('Which site would you like to correct file permissions for', 2, [1 => 'hellfishmedia.com', 2 => 'staging.hellfishmedia.com'])
        ->expectsOutput('==> File permissions queued for correction.')
        ->assertExitCode(0);
});
