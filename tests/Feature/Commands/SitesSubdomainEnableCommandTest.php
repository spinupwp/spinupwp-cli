<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('enable the spinupwp subdomain', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'subdomain' => ['enabled' => false]]]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/spinupwp-subdomain', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:subdomain:enable 1')
        ->expectsOutput('==> SpinupWP subdomain queued for enabling.')
        ->assertExitCode(0);
});

test('enable the spinupwp subdomain shows the api error when already enabled', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'subdomain' => ['enabled' => true]]]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/spinupwp-subdomain', [])->andReturn(
        new Response(400, [], json_encode(['message' => 'The SpinupWP subdomain is already enabled.']))
    );

    $this->artisan('sites:subdomain:enable 1')
        ->expectsOutput('The SpinupWP subdomain is already enabled.')
        ->assertExitCode(1);
});

test('enable the spinupwp subdomain only offers sites without it', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'subdomain' => ['enabled' => true, 'url' => 'https://hellfish.spinupwp.site']],
        ]))
    );

    $this->artisan('sites:subdomain:enable')
        ->expectsOutput('There are no sites with the SpinupWP subdomain disabled.')
        ->assertExitCode(0);
});
