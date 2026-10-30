<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('disable the spinupwp subdomain', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'subdomain' => ['enabled' => true]]]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/spinupwp-subdomain', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:subdomain:disable 1')
        ->expectsConfirmation('Are you sure you want to disable the SpinupWP subdomain for "hellfishmedia.com"?', 'yes')
        ->expectsOutput('==> SpinupWP subdomain queued for disabling.')
        ->assertExitCode(0);
});

test('disable the spinupwp subdomain with force option does not prompt', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'subdomain' => ['enabled' => true]]]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/spinupwp-subdomain', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:subdomain:disable 1 --force')
        ->expectsOutput('==> SpinupWP subdomain queued for disabling.')
        ->assertExitCode(0);
});

test('disable the spinupwp subdomain only offers sites with it enabled', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'subdomain' => ['enabled' => false]],
        ]))
    );

    $this->artisan('sites:subdomain:disable')
        ->expectsOutput('There are no sites with the SpinupWP subdomain enabled.')
        ->assertExitCode(0);
});
