<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->event = [
        'id'           => 2,
        'initiated_by' => 'Abe Simpson',
        'server_id'    => 1,
        'name'         => 'Rebooting server',
        'status'       => 'failed',
        'output'       => 'Timed out',
        'created_at'   => '29 Oct 2026 09:00',
        'started_at'   => '29 Oct 2026 09:00',
        'finished_at'  => '29 Oct 2026 09:10',
    ];

    $this->clientMock->shouldReceive('request')->with('GET', 'events/2', [])->andReturn(
        new Response(200, [], json_encode(['data' => $this->event]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('get an event as json', function () {
    $this->artisan('events:get 2')
        ->expectsOutput(json_encode($this->event, JSON_PRETTY_PRINT))
        ->assertExitCode(0);
});

test('get an event as a table', function () {
    $this->artisan('events:get 2 --format=table')
        ->expectsTable([], [
            ['ID', '2'],
            ['Name', 'Rebooting server'],
            ['Status', 'Failed'],
            ['Server ID', '1'],
            ['Initiated By', 'Abe Simpson'],
            ['Created At', '29 Oct 2026 09:00'],
            ['Started At', '29 Oct 2026 09:00'],
            ['Finished At', '29 Oct 2026 09:10'],
            ['Output', 'Timed out'],
        ])
        ->assertExitCode(0);
});
