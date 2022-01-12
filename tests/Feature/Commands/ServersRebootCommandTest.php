<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'hellfish-media']]))
    );

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/1/reboot', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('reboot a server', function () {
    $this->artisan('servers:reboot 1')
        ->expectsConfirmation('Are you sure you want to reboot "hellfish-media"?', 'yes')
        ->expectsOutput('==> Server queued for reboot.');
});

test('reboot a server with force option', function () {
    $this->artisan('servers:reboot 1 --force')
        ->expectsOutput('==> Server queued for reboot.');
});

test('reboot all servers', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'name' => 'hellfish-media'],
            ['id' => 2, 'name' => 'staging.hellfish-media'],
        ]))
    );
    $this->clientMock->shouldReceive('request')->with('POST', 'servers/2/reboot', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '101']))
    );
    $this->artisan('servers:reboot --all')
        ->expectsConfirmation('Are you sure you want to reboot all servers?', 'yes')
        ->expectsOutput('==> Server queued for reboot.')
        ->expectsOutput('==> Server queued for reboot.');
});
