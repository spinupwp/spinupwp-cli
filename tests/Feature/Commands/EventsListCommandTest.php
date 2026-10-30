<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->events = [
        ['id' => 3, 'initiated_by' => 'SpinupWP API', 'server_id' => 1, 'site_id' => 2, 'name' => 'Deploying site hellfish.media', 'status' => 'deployed', 'output' => '', 'created_at' => '30 Oct 2026 14:05', 'started_at' => '30 Oct 2026 14:05', 'finished_at' => '30 Oct 2026 14:06'],
        ['id' => 2, 'initiated_by' => 'Abe Simpson', 'server_id' => 1, 'name' => 'Rebooting server', 'status' => 'failed', 'output' => 'Timed out', 'created_at' => '29 Oct 2026 09:00', 'started_at' => '29 Oct 2026 09:00', 'finished_at' => '29 Oct 2026 09:10'],
    ];
});

afterEach(function () {
    deleteTestConfigFile();
});

test('list events as json', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'events?page=1&limit=20', [])->andReturn(
        new Response(200, [], listResponseJson($this->events))
    );

    $this->artisan('events:list')
        ->expectsOutput(json_encode($this->events, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list events as a table', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'events?page=1&limit=20', [])->andReturn(
        new Response(200, [], listResponseJson($this->events))
    );

    $this->artisan('events:list --format=table')
        ->expectsTable(['ID', 'Name', 'Status', 'Server ID', 'Created At'], [
            [3, 'Deploying site hellfish.media', 'Deployed', 1, '30 Oct 2026 14:05'],
            [2, 'Rebooting server', 'Failed', 1, '29 Oct 2026 09:00'],
        ])
        ->assertExitCode(0);
});

test('list events stops at the limit without fetching more pages', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'events?page=1&limit=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data'       => [$this->events[0]],
            'pagination' => ['previous' => null, 'next' => 'https://api.spinupwp.app/v1/events?page=2', 'per_page' => 1, 'count' => 2],
        ]))
    );

    $this->artisan('events:list --limit=1')
        ->expectsOutput(json_encode([$this->events[0]], JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('list events rejects a limit below one', function () {
    $this->artisan('events:list --limit=0')
        ->expectsOutput('The --limit option must be at least 1.')
        ->assertExitCode(1);
});
