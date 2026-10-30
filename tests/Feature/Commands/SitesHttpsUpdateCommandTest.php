<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

test('replace a custom https certificate', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'https' => ['enabled' => true]]]))
    );

    $dir = sys_get_temp_dir();
    file_put_contents("{$dir}/spinupwp_test_cert.pem", "CERTIFICATE\n");
    file_put_contents("{$dir}/spinupwp_test_cert.key", "PRIVATE KEY\n");

    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/https', [
        'json' => ['type' => 'custom', 'certificate' => 'CERTIFICATE', 'private_key' => 'PRIVATE KEY'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    try {
        $this->artisan("sites:https:update 1 --type=custom --certificate={$dir}/spinupwp_test_cert.pem --private-key={$dir}/spinupwp_test_cert.key")
            ->expectsOutput('==> HTTPS certificate queued for update.')
            ->assertExitCode(0);
    } finally {
        unlink("{$dir}/spinupwp_test_cert.pem");
        unlink("{$dir}/spinupwp_test_cert.key");
    }
});

test("switch to a let's encrypt certificate", function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'https' => ['enabled' => true]]]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/https', [
        'json' => ['type' => 'webroot'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:https:update 1 --type=letsencrypt')
        ->expectsOutput('==> HTTPS certificate queued for update.')
        ->assertExitCode(0);
});

test('update https only offers sites with https enabled', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 1, 'domain' => 'hellfishmedia.com', 'https' => ['enabled' => false]],
        ]))
    );

    $this->artisan('sites:https:update')
        ->expectsOutput('There are no sites with HTTPS enabled.')
        ->assertExitCode(0);
});
