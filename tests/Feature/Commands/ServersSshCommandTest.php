<?php

use App\Repositories\ConfigRepository;
use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('ssh command with server ID supplied and no default SSH user', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'         => 1,
                'name'       => 'hellfishmedia',
                'ip_address' => '123.123.123.123',
                'ssh_port'   => 22,
            ],
        ]))
    );

    $this->artisan('servers:ssh 1 johndoe')
        ->expectsQuestion('Do you want to set "johndoe" as your default SSH user? (y/n)', 'y')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [johndoe]...')
        ->assertExitCode(255);
});

test('ssh command with server ID supplied and no SSH user', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'         => 1,
                'name'       => 'hellfishmedia',
                'ip_address' => '123.123.123.123',
                'ssh_port'   => 22,
            ],
        ]))
    );

    $this->artisan('servers:ssh 1')
        ->expectsQuestion('Which user would you like to connect as', 'johndoe')
        ->expectsQuestion('Do you want to set "johndoe" as your default SSH user? (y/n)', 'y')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [johndoe]...')
        ->assertExitCode(255);
});

test('ssh command with no server ID supplied', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers?page=1&limit=100', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                [
                    'id'         => 1,
                    'name'       => 'hellfishmedia',
                    'ip_address' => '123.123.123.123',
                    'ssh_port'   => 22,
                ],
            ],
            'pagination' => [
                'previous' => null,
                'next'     => null,
                'count'    => 1,
            ],
        ]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'         => 1,
                'name'       => 'hellfishmedia',
                'ip_address' => '123.123.123.123',
                'ssh_port'   => 22,
            ],
        ]))
    );

    $this->artisan('servers:ssh')
        ->expectsQuestion('Which server would you like to start an SSH session for', '1')
        ->expectsQuestion('Which user would you like to connect as', 'johndoe')
        ->expectsQuestion('Do you want to set "johndoe" as your default SSH user? (y/n)', 'y')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [johndoe]...')
        ->assertExitCode(255);
});

test('ssh command with server ID supplied and default SSH user', function () {
    setTestConfigFile([
        'ssh_user' => 'janedoe',
    ]);

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'         => 1,
                'name'       => 'hellfishmedia',
                'ip_address' => '123.123.123.123',
                'ssh_port'   => 22,
            ],
        ]))
    );

    $this->artisan('servers:ssh 1')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [janedoe]...')
        ->assertExitCode(255);
});
