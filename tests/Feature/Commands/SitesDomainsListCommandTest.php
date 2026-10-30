<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfish.media']]))
    );

    $this->domains = [
        [
            'id'         => 1,
            'domain'     => 'www.hellfish.media',
            'redirect'   => ['enabled' => true, 'type' => 301, 'destination' => 'hellfish.media'],
            'created_at' => '2026-10-01T12:00:00.000000Z',
        ],
        [
            'id'         => 2,
            'domain'     => 'hellfishmedia.com',
            'redirect'   => ['enabled' => false],
            'created_at' => '2026-10-02T12:00:00.000000Z',
        ],
    ];
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list additional domains as json', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/domains', [])->andReturn(
        new Response(200, [], json_encode(['data' => $this->domains]))
    );

    $this->artisan('sites:domains:list 1')
        ->expectsOutput(json_encode($this->domains, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list additional domains as a table', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/domains', [])->andReturn(
        new Response(200, [], json_encode(['data' => $this->domains]))
    );

    $this->artisan('sites:domains:list 1 --format=table')
        ->expectsTable(['ID', 'Domain', 'Redirect', 'Created At'], [
            [1, 'www.hellfish.media', '301 to hellfish.media', '2026-10-01T12:00:00.000000Z'],
            [2, 'hellfishmedia.com', 'No', '2026-10-02T12:00:00.000000Z'],
        ])
        ->assertExitCode(0);
});

test('list additional domains when there are none', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites/1/domains', [])->andReturn(
        new Response(200, [], json_encode(['data' => []]))
    );

    $this->artisan('sites:domains:list 1')
        ->expectsOutput('No additional domains found.')
        ->assertExitCode(0);
});
