<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media']]))
    );

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1/domains', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            ['id' => 5, 'domain' => 'www.hellfish.media', 'redirect' => ['enabled' => true, 'type' => 301, 'destination' => 'hellfish.media']],
            ['id' => 6, 'domain' => 'hellfishmedia.com', 'redirect' => ['enabled' => false]],
        ]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('delete a domain chosen by name', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/domains/6', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:domains:delete 1 hellfishmedia.com')
        ->expectsConfirmation('Are you sure you want to delete "hellfishmedia.com" from "hellfish.media"?', 'yes')
        ->expectsOutput('==> Domain "hellfishmedia.com" queued for deletion.')
        ->assertExitCode(0);
});

test('delete a domain chosen interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/domains/5', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:domains:delete 1 --force')
        ->expectsChoice('Which domain would you like to delete', 5, [5 => 'www.hellfish.media', 6 => 'hellfishmedia.com'])
        ->expectsOutput('==> Domain "www.hellfish.media" queued for deletion.')
        ->assertExitCode(0);
});

test('delete a domain does nothing when not confirmed', function () {
    $this->clientMock->shouldNotReceive('request')->with('DELETE', 'sites/1/domains/6', []);

    $this->artisan('sites:domains:delete 1 6')
        ->expectsConfirmation('Are you sure you want to delete "hellfishmedia.com" from "hellfish.media"?', 'no')
        ->assertExitCode(0);
});
