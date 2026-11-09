<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

function mockLogsSiteAndServer($clientMock): void
{
    $clientMock->shouldReceive('request')->once()->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'        => 1,
                'server_id' => 1,
                'domain'    => 'hellfishmedia.com',
                'site_user' => 'hellfish',
            ],
        ]))
    );

    $clientMock->shouldReceive('request')->once()->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'         => 1,
                'name'       => 'hellfishmedia',
                'ip_address' => '123.123.123.123',
                'ssh_port'   => 22,
            ],
        ]))
    );
}

test('logs command with site ID and log supplied', function () {
    mockLogsSiteAndServer($this->clientMock);

    $this->artisan('sites:logs 1 --log=error.log --lines=50 --follow --profile=johndoe')
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [hellfish]...')
        ->assertExitCode(255);
});

test('logs command prompts for the site and log when not supplied', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                [
                    'id'        => 1,
                    'server_id' => 1,
                    'domain'    => 'hellfishmedia.com',
                    'site_user' => 'hellfish',
                ],
            ],
            'pagination' => [
                'previous' => null,
                'next'     => null,
                'count'    => 1,
            ],
        ]))
    );

    mockLogsSiteAndServer($this->clientMock);

    $this->artisan('sites:logs --profile=johndoe')
        ->expectsQuestion('Which site would you like to view logs for', '1')
        ->expectsChoice('Which log would you like to view', 'access.log', ['debug.log', 'error.log', 'access.log'])
        ->expectsOutput('Establishing a secure connection to [hellfishmedia] as [hellfish]...')
        ->assertExitCode(255);
});

test('logs command rejects an unknown log', function () {
    $this->artisan('sites:logs 1 --log="debug.log; rm -rf ~" --profile=johndoe')
        ->expectsOutput('The --log option must be one of: debug.log, error.log, access.log.')
        ->assertExitCode(1);
});

test('logs command rejects invalid line counts', function (string $lines) {
    $this->artisan("sites:logs 1 --log=debug.log --lines={$lines} --profile=johndoe")
        ->expectsOutput('The --lines option must be a positive whole number.')
        ->assertExitCode(1);
})->with(['0', '-5', '10abc']);
