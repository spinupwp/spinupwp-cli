<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media']]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('add a domain that serves the site', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/domains', [
        'json' => ['domain' => 'hellfishmedia.com', 'redirect' => ['enabled' => false]],
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => 100,
        'data'     => ['id' => 2, 'domain' => 'hellfishmedia.com', 'redirect' => ['enabled' => false]],
    ])));

    $this->artisan('sites:domains:add 1 --domain=hellfishmedia.com --force')
        ->expectsOutput('==> Domain "hellfishmedia.com" queued for adding.')
        ->assertExitCode(0);
});

test('add a domain that redirects to the primary domain by default', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/domains', [
        'json' => [
            'domain'   => 'www.hellfish.media',
            'redirect' => ['enabled' => true, 'type' => 301, 'destination' => 'hellfish.media'],
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100, 'data' => []])));

    $this->artisan('sites:domains:add 1 --domain=www.hellfish.media --redirect --force')
        ->expectsOutput('==> Domain "www.hellfish.media" queued for adding.')
        ->assertExitCode(0);
});

test('add a redirecting domain interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/domains', [
        'json' => [
            'domain'   => 'old.hellfish.media',
            'redirect' => ['enabled' => true, 'type' => 302, 'destination' => 'https://hellfish.media/blog'],
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100, 'data' => []])));

    $this->artisan('sites:domains:add 1')
        ->expectsQuestion('Domain', 'old.hellfish.media')
        ->expectsConfirmation('Redirect this domain to "hellfish.media" or another URL?', 'yes')
        ->expectsChoice('Redirect type', '302', [
            '301' => 'Permanent (301)',
            '302' => 'Temporary (302)',
            '307' => 'Temporary, keeping the request method (307)',
            '308' => 'Permanent, keeping the request method (308)',
        ])
        ->expectsQuestion('Redirect to', 'https://hellfish.media/blog')
        ->expectsOutput('==> Domain "old.hellfish.media" queued for adding.')
        ->assertExitCode(0);
});

test('add a redirecting domain when the prompt answers with the redirect label', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/domains', [
        'json' => [
            'domain'   => 'www.hellfish.media',
            'redirect' => ['enabled' => true, 'type' => 308, 'destination' => 'hellfish.media'],
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100, 'data' => []])));

    // Symfony's choice prompt returns the label for integer-keyed choices, as it does here in real use.
    $this->artisan('sites:domains:add 1')
        ->expectsQuestion('Domain', 'www.hellfish.media')
        ->expectsConfirmation('Redirect this domain to "hellfish.media" or another URL?', 'yes')
        ->expectsQuestion('Redirect type', 'Permanent, keeping the request method (308)')
        ->expectsQuestion('Redirect to', 'hellfish.media')
        ->expectsOutput('==> Domain "www.hellfish.media" queued for adding.')
        ->assertExitCode(0);
});

test('add a domain rejects an unknown redirect type', function () {
    $this->artisan('sites:domains:add 1 --domain=www.hellfish.media --redirect --redirect-type=303 --force')
        ->expectsOutput('The --redirect-type option must be 301, 302, 307 or 308.')
        ->assertExitCode(1);
});
