<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list ssh keys', function () {
    $sshKeys = [
        ['id' => 1, 'name' => 'Laptop', 'fingerprint' => 'SHA256:abc', 'publickey' => 'ssh-ed25519 AAAA', 'created_at' => '2024-07-01T12:00:00.000000Z'],
    ];

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'ssh-keys?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson($sshKeys))
    );

    $this->artisan('ssh-key:list')
        ->expectsOutput(json_encode($sshKeys, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list ssh keys as a table', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'ssh-keys?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'name' => 'Laptop', 'fingerprint' => 'SHA256:abc', 'publickey' => 'ssh-ed25519 AAAA', 'created_at' => '2024-07-01T12:00:00.000000Z'],
        ]))
    );

    $this->artisan('ssh-key:list --format=table')
        ->expectsTable(['ID', 'Name', 'Fingerprint', 'Created At'], [
            [1, 'Laptop', 'SHA256:abc', '2024-07-01T12:00:00.000000Z'],
        ])
        ->assertExitCode(0);
});

test('list ssh keys with specified fields', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'ssh-keys?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'name' => 'Laptop', 'fingerprint' => 'SHA256:abc', 'publickey' => 'ssh-ed25519 AAAA', 'created_at' => '2024-07-01T12:00:00.000000Z'],
        ]))
    );

    $this->artisan('ssh-key:list --format=table --fields=id,publickey')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'no')
        ->expectsTable(['ID', 'Public Key'], [
            [1, 'ssh-ed25519 AAAA'],
        ])
        ->assertExitCode(0);
});
