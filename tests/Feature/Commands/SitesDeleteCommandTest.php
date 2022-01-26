<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media']]))
    );

    $this->clientMock->shouldReceive('request')->with('DELETE', 'sites/1', [
        'form_params' => [
            'delete_database' => false,
            'delete_backups'  => false,
        ],
    ])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('delete a site without force option', function () {
    $this->artisan('sites:delete 1')
        ->expectsConfirmation('Do you wish to continue?', 'yes')
        ->expectsOutput('==> Site queued for deletion.');
});

test('delete a site with force option', function () {
    $this->artisan('sites:delete 1 --force')
        ->expectsOutput('==> Site queued for deletion.');
});

test('delete a site without force option and cancel deletion', function () {
    $this->artisan('sites:delete 1')
        ->expectsConfirmation('Do you wish to continue?', 'no')
        ->doesntExpectOutput('==> Site queued for deletion.');
});

test('delete a site with delete-database and delete-backups options', function () {
    $this->clientMock->shouldReceive('request')->with('DELETE', 'sites/1', [
        'form_params' => [
            'delete_database' => true,
            'delete_backups'  => true,
        ],
    ])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );

    $this->artisan('sites:delete 1 --delete-database --delete-backups --force')
        ->expectsOutput('==> Site queued for deletion.');
});
