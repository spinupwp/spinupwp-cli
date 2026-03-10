<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('POST', 'sites/1/wp-cli', [
        'form_params' => [
            'commands' => ['core version'],
        ],
    ])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('wp-cli command with site ID and command supplied', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'events/100', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'     => 100,
                'status' => 'deployed',
                'output' => "$ core version\n6.4.2",
            ],
        ]))
    );

    $this->artisan('sites:wp-cli', ['site_id' => '1', '--command' => ['core version']])
        ->expectsOutput('==> WP-CLI commands queued for execution.')
        ->assertExitCode(0);
});

test('wp-cli command with interactive site selection', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                [
                    'id'     => 1,
                    'domain' => 'hellfishmedia.com',
                ],
            ],
            'pagination' => [
                'previous' => null,
                'next'     => null,
                'count'    => 1,
            ],
        ]))
    );

    $this->clientMock->shouldReceive('request')->with('GET', 'events/100', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'     => 100,
                'status' => 'deployed',
                'output' => "$ core version\n6.4.2",
            ],
        ]))
    );

    $this->artisan('sites:wp-cli', ['--command' => ['core version']])
        ->expectsQuestion('Which site would you like to run WP-CLI commands on', '1')
        ->expectsOutput('==> WP-CLI commands queued for execution.')
        ->assertExitCode(0);
});

test('wp-cli command with multiple commands', function () {
    $this->clientMock->shouldReceive('request')->with('POST', 'sites/1/wp-cli', [
        'form_params' => [
            'commands' => ['core version', 'plugin list --status=active'],
        ],
    ])->andReturn(
        new Response(200, [], json_encode(['event_id' => 101]))
    );

    $this->clientMock->shouldReceive('request')->with('GET', 'events/101', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'     => 101,
                'status' => 'deployed',
                'output' => "$ core version\n6.4.2\n\n$ plugin list --status=active\nName\tStatus\tVersion",
            ],
        ]))
    );

    $this->artisan('sites:wp-cli', ['site_id' => '1', '--command' => ['core version', 'plugin list --status=active']])
        ->expectsOutput('==> WP-CLI commands queued for execution.')
        ->assertExitCode(0);
});
