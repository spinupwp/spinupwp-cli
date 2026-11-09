<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->sshKeys = [
        ['id' => 3, 'name' => 'CI deploy', 'fingerprint' => '7e:2c:6e:f8:4c:b1:20:7c:e3:f5:89:4a:68:2b:c9:62'],
        ['id' => 4, 'name' => 'Engineer', 'fingerprint' => '1a:9f:00:3b:52:6d:e1:47:88:cc:20:5e:91:0a:7b:d4'],
    ];
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list the site user ssh keys as json', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media', 'ssh_keys' => $this->sshKeys]]))
    );

    $this->artisan('sites:ssh-keys:list 1')
        ->expectsOutput(json_encode($this->sshKeys, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list the site user ssh keys as a table', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media', 'ssh_keys' => $this->sshKeys]]))
    );

    $this->artisan('sites:ssh-keys:list 1 --format=table')
        ->expectsTable(['ID', 'Name', 'Fingerprint'], [
            [3, 'CI deploy', '7e:2c:6e:f8:4c:b1:20:7c:e3:f5:89:4a:68:2b:c9:62'],
            [4, 'Engineer', '1a:9f:00:3b:52:6d:e1:47:88:cc:20:5e:91:0a:7b:d4'],
        ])
        ->assertExitCode(0);
});

test('list the site user ssh keys when there are none', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media', 'ssh_keys' => []]]))
    );

    $this->artisan('sites:ssh-keys:list 1')
        ->expectsOutput('No SSH keys are authorized for the site user.')
        ->assertExitCode(0);
});
