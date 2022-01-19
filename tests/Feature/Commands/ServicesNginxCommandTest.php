<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'hellfish-media']]))
    );

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/1/services/nginx/restart', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('restart nginx for a server', function () {
    $this->artisan('services:nginx 1')
        ->expectsConfirmation('Are you sure you want to restart Nginx on "hellfish-media"?', 'yes')
        ->expectsOutput('==> Server queued for Nginx restart.');
});

test('restart nginx for a server with force option', function () {
    $this->artisan('services:nginx 1 --force')
        ->expectsOutput('==> Server queued for Nginx restart.');
});

test('restart nginx on all servers', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'name' => 'hellfish-media'],
            ['id' => 2, 'name' => 'staging.hellfish-media'],
        ]))
    );
    $this->clientMock->shouldReceive('request')->with('POST', 'servers/2/services/nginx/restart', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '101']))
    );
    $this->artisan('services:nginx --all')
        ->expectsConfirmation('Are you sure you want to restart Nginx on all servers?', 'yes')
        ->expectsOutput('==> Servers queued for Nginx restart.');
});
