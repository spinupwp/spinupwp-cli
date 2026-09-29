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

test('update site user with ssh keys', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/site-user', [
        'json' => ['authentication' => 'publickey', 'ssh_key_ids' => [1, 3]],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:site-user 1 --authentication=publickey --ssh-key-id=1 --ssh-key-id=3')
        ->expectsOutput('==> Site user queued for update.')
        ->assertExitCode(0);
});

test('update site user selecting ssh keys interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'ssh-keys?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 4, 'name' => 'Laptop'],
            ['id' => 7, 'name' => 'Desktop'],
        ]))
    );
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/site-user', [
        'json' => ['authentication' => 'publickey', 'ssh_key_ids' => [4, 7]],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:site-user 1')
        ->expectsQuestion('How should the site user authenticate over SSH?', 'publickey')
        ->expectsQuestion('Which SSH keys should be authorized for the site user? (comma-separated)', ['4', '7'])
        ->expectsOutput('==> Site user queued for update.')
        ->assertExitCode(0);
});

test('update site user with a password', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/site-user', [
        'json' => ['authentication' => 'password', 'password' => 'supersecret'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:site-user 1 --authentication=password --password=supersecret')
        ->expectsOutput('==> Site user queued for update.')
        ->assertExitCode(0);
});

test('update site user asks for a password', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/site-user', [
        'json' => ['authentication' => 'password', 'password' => 'supersecret'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:site-user 1 --authentication=password')
        ->expectsQuestion('Password', 'supersecret')
        ->expectsOutput('==> Site user queued for update.')
        ->assertExitCode(0);
});

test('update site user with force option requires ssh key ids', function () {
    $this->artisan('sites:site-user 1 --force')
        ->expectsOutput('The --ssh-key-id option is required when running with --force.')
        ->assertExitCode(1);
});

test('update site user with force option and ssh key ids', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/site-user', [
        'json' => ['authentication' => 'publickey', 'ssh_key_ids' => [1]],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:site-user 1 --ssh-key-id=1 --force')
        ->expectsOutput('==> Site user queued for update.')
        ->assertExitCode(0);
});
