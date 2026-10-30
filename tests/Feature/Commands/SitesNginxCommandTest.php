<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'         => 1,
            'domain'     => 'hellfishmedia.com',
            'basic_auth' => ['enabled' => true],
        ]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('update nginx settings', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/nginx', [
        'json' => [
            'uploads_directory_protected' => true,
            'xmlrpc_protected'            => false,
            'public_folder'               => '/public',
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_ids' => [100, 101]])));

    $this->artisan('sites:nginx 1 --uploads-directory-protected=true --xmlrpc-protected=false --public-folder=/public')
        ->expectsOutput('==> Nginx settings queued for update.')
        ->assertExitCode(0);
});

test('update nginx settings without changes', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/nginx', [
        'json' => ['subdirectory_rewrite_in_place' => true],
    ])->andReturn(new Response(200, [], json_encode(['event_ids' => []])));

    $this->artisan('sites:nginx 1 --subdirectory-rewrite=1')
        ->expectsOutput('==> Nginx settings are already up to date.')
        ->assertExitCode(0);
});
