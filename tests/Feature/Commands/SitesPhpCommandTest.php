<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'php_version' => '8.3']]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('change the php version with an option', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/php', [
        'json' => ['php_version' => '8.5'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:php 1 --php-version=8.5')
        ->expectsOutput('==> PHP 8.5 queued for "hellfishmedia.com".')
        ->assertExitCode(0);
});

test('change the php version interactively without offering the current version', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/php', [
        'json' => ['php_version' => '8.2'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:php 1')
        ->expectsChoice('Which PHP version should "hellfishmedia.com" run under (currently 8.3)?', '8.2', ['8.5', '8.4', '8.2', '8.1', '8.0', '7.4'])
        ->expectsOutput('==> PHP 8.2 queued for "hellfishmedia.com".')
        ->assertExitCode(0);
});

test('change the php version with force option requires the version', function () {
    $this->clientMock->shouldNotReceive('request')->with('PUT', 'sites/1/php', Mockery::any());

    $this->artisan('sites:php 1 --force')
        ->expectsOutput('The --php-version option is required when running with --force.')
        ->assertExitCode(1);
});

test('change the php version with force option and a version', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/php', [
        'json' => ['php_version' => '8.2'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:php 1 --php-version=8.2 --force')
        ->expectsOutput('==> PHP 8.2 queued for "hellfishmedia.com".')
        ->assertExitCode(0);
});

test('change the php version shows validation errors', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/php', [
        'json' => ['php_version' => '8.3'],
    ])->andReturn(new Response(422, [], json_encode([
        'message' => 'The given data was invalid.',
        'errors'  => ['php_version' => ['The site is already running this PHP version.']],
    ])));

    $this->artisan('sites:php 1 --php-version=8.3')
        ->expectsOutput('Validation errors occurred.')
        ->assertExitCode(1);
});
