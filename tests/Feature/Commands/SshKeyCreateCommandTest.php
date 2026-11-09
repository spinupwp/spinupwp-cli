<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->keyDir = sys_get_temp_dir() . '/spinupwp-cli-ssh-key-test-' . uniqid();
    mkdir($this->keyDir);
    file_put_contents("{$this->keyDir}/id_ed25519.pub", "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIGx laptop\n");
    file_put_contents("{$this->keyDir}/id_ed25519", "-----BEGIN OPENSSH PRIVATE KEY-----\nb3BlbnNzaC1rZXktdjEAAAAA\n-----END OPENSSH PRIVATE KEY-----\n");
});

afterEach(function () {
    deleteTestConfigFile();
    array_map('unlink', glob("{$this->keyDir}/*"));
    rmdir($this->keyDir);
});

test('add an ssh key from a public key file', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'ssh-keys', [
        'json' => ['name' => 'Laptop', 'publickey' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIGx laptop'],
    ])->andReturn(new Response(201, [], json_encode(['data' => [
        'id'          => 7,
        'name'        => 'Laptop',
        'fingerprint' => 'cf:2b:94:e3:22:3a:75:ca:94:3b:35:83:22:1d:ce:69',
        'publickey'   => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIGx laptop',
    ]])));

    $this->artisan("ssh-key:create --name=Laptop --public-key={$this->keyDir}/id_ed25519.pub")
        ->expectsOutput('==> SSH key "Laptop" added to your team.')
        ->assertExitCode(0);
});

test('add an ssh key asks for the name and file', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'ssh-keys', [
        'json' => ['name' => 'Laptop', 'publickey' => 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIGx laptop'],
    ])->andReturn(new Response(201, [], json_encode(['data' => ['id' => 7, 'name' => 'Laptop', 'fingerprint' => 'cf:2b']])));

    $this->artisan('ssh-key:create')
        ->expectsQuestion('Name', 'Laptop')
        ->expectsQuestion('Path to the public key file', "{$this->keyDir}/id_ed25519.pub")
        ->expectsOutput('==> SSH key "Laptop" added to your team.')
        ->assertExitCode(0);
});

test('add an ssh key refuses to send a private key', function () {
    $this->clientMock->shouldNotReceive('request')->with('POST', 'ssh-keys', Mockery::any());

    $this->artisan("ssh-key:create --name=Laptop --public-key={$this->keyDir}/id_ed25519")
        ->expectsOutput("\"{$this->keyDir}/id_ed25519\" is a private key. Use the public key file, which usually ends in .pub.")
        ->assertExitCode(1);
});

test('add an ssh key from a file that does not exist', function () {
    $this->clientMock->shouldNotReceive('request')->with('POST', 'ssh-keys', Mockery::any());

    $this->artisan("ssh-key:create --name=Laptop --public-key={$this->keyDir}/missing.pub")
        ->expectsOutput("Unable to read the file \"{$this->keyDir}/missing.pub\".")
        ->assertExitCode(1);
});

test('add an ssh key with force requires the name and the public key file', function () {
    $this->clientMock->shouldNotReceive('request')->with('POST', 'ssh-keys', Mockery::any());

    $this->artisan("ssh-key:create --force --public-key={$this->keyDir}/id_ed25519.pub")
        ->expectsOutput('The --name option is required when running with --force.')
        ->assertExitCode(1);

    $this->artisan('ssh-key:create --force --name=Laptop')
        ->expectsOutput('The --public-key option is required when running with --force.')
        ->assertExitCode(1);
});
