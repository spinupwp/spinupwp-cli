<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                'id'     => 1,
                'domain' => 'hellfishmedia.com',
                'git'    => [
                    'enabled' => true,
                ],
            ],
        ]))
    );

    $this->clientMock->shouldReceive('request')->with('POST', 'sites/1/git/deploy', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => '100']))
    );
});

test('site git deploy command with site ID supplied', function () {
    $this->artisan('sites:deploy 1')
        ->expectsOutput('Deploying site "hellfishmedia.com". Event ID: 100.')
        ->assertExitCode(0);
});

test('site git deploy command', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => [
                [
                    'id'     => 1,
                    'domain' => 'hellfishmedia.com',
                    'git'    => [
                        'enabled' => true,
                    ],
                ],
            ],
            'pagination' => [
                'previous' => null,
                'next'     => null,
                'count'    => 1,
            ],
        ]))
    );

    $this->artisan('sites:deploy')
        ->expectsQuestion('Which site would you like to deploy?', '1')
        ->expectsOutput('Deploying site "hellfishmedia.com". Event ID: 100.')
        ->assertExitCode(0);
});
