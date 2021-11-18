<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('ssh command with site ID supplied', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'        => 1,
                'server_id' => 1,
                'domain'    => 'hellfishmedia.com',
                'site_user' => 'hellfish',
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

    $this->artisan('sites:ssh 1 --profile=johndoe')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [hellfish]...')
        ->assertExitCode(255);
});

test('ssh command with no site ID', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                [
                    'id'        => 1,
                    'server_id' => 1,
                    'domain'    => 'hellfishmedia.com',
                    'site_user' => 'hellfish',
                ],
            ],
        ]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'        => 1,
                'server_id' => 1,
                'domain'    => 'hellfishmedia.com',
                'site_user' => 'hellfish',
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

    $this->artisan('sites:ssh --profile=johndoe')
        ->expectsQuestion('Which site would you like to start an SSH session for', '1')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [hellfish]...')
        ->assertExitCode(255);
});
