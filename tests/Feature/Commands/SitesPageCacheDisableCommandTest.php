<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'page_cache' => ['enabled' => true]]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('disable page cache', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/page-cache', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:page-cache:disable 1')
        ->expectsConfirmation('Are you sure you want to disable the page cache for "hellfishmedia.com"?', 'yes')
        ->expectsOutput('==> Page cache queued for disabling.')
        ->assertExitCode(0);
});
