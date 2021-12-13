<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
    $this->clientMock->shouldReceive('request')->with('DELETE', 'servers/1', [
        'form_params' => ['delete_server_on_provider' => false],
    ])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('delete a server with force option', function () {
    $this->artisan('servers:delete 1 --force')->expectsOutput('Server deletion in progress. Event ID: 100');
});

test('delete a server without force option', function () {
    $this->artisan('servers:delete 1')
        ->expectsConfirmation('Are you sure you want to delete this server?', 'yes')
        ->expectsOutput('Server deletion in progress. Event ID: 100');
});

test('delete a server without force option and cancel deletion', function () {
    $this->artisan('servers:delete 1')
        ->expectsConfirmation('Are you sure you want to delete this server?', 'no')
        ->doesntExpectOutput('Server deletion in progress. Event ID: 100');
});

test('delete a server on provider', function () {
    $this->clientMock->shouldReceive('request')->with('DELETE', 'servers/1', [
        'form_params' => ['delete_server_on_provider' => true],
    ])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
    $this->artisan('servers:delete 1 --delete-on-provider')
        ->expectsConfirmation('Are you sure you want to delete this server?', 'yes')
        ->expectsOutput('Server deletion in progress. Event ID: 100');
});
