<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('ssh key json get command', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'ssh-key', [])->andReturn(
        new Response(200, [], json_encode(['key' => 'ssh-rsa ...']))
    );
    $this->artisan('ssh-key:get')->expectsOutput(json_encode(['key' => 'ssh-rsa ...'], JSON_PRETTY_PRINT));
});

test('ssh key table get command', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'ssh-key', [])->andReturn(
        new Response(200, [], json_encode(['key' => 'ssh-rsa ...'], ))
    );
    $this->artisan('ssh-key:get --format=table')->expectsTable([], [
        ['key', 'ssh-rsa ...'],
    ]);
});
