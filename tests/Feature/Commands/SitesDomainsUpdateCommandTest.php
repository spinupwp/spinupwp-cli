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

test('stop redirecting a domain chosen by name', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/domains/5', [
        'json' => ['redirect' => ['enabled' => false]],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100, 'data' => []])));

    $this->artisan('sites:domains:update 1 www.hellfish.media --redirect=false')
        ->expectsOutput('==> Domain "www.hellfish.media" queued for update.')
        ->assertExitCode(0);
});

test('change a redirect chosen by id, keeping the current destination', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/domains/5', [
        'json' => ['redirect' => ['enabled' => true, 'type' => 308, 'destination' => 'hellfish.media']],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100, 'data' => []])));

    $this->artisan('sites:domains:update 1 5 --redirect=true --redirect-type=308 --force')
        ->expectsOutput('==> Domain "www.hellfish.media" queued for update.')
        ->assertExitCode(0);
});

test('update a domain interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/domains/6', [
        'json' => ['redirect' => ['enabled' => true, 'type' => 301, 'destination' => 'hellfish.media']],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100, 'data' => []])));

    $this->artisan('sites:domains:update 1')
        ->expectsChoice('Which domain would you like to update', 6, [5 => 'www.hellfish.media', 6 => 'hellfishmedia.com'])
        ->expectsConfirmation('Redirect "hellfishmedia.com" to "hellfish.media" or another URL?', 'yes')
        ->expectsChoice('Redirect type', '301', [
            '301' => 'Permanent (301)',
            '302' => 'Temporary (302)',
            '307' => 'Temporary, keeping the request method (307)',
            '308' => 'Permanent, keeping the request method (308)',
        ])
        ->expectsQuestion('Redirect to', 'hellfish.media')
        ->expectsOutput('==> Domain "hellfishmedia.com" queued for update.')
        ->assertExitCode(0);
});

test('update a domain that does not exist', function () {
    $this->artisan('sites:domains:update 1 nope.hellfish.media --redirect=false')
        ->expectsOutput('No additional domain "nope.hellfish.media" found on "hellfish.media".')
        ->assertExitCode(1);
});
