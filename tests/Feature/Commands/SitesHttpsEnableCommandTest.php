<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'https' => ['enabled' => false]]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test("enable https with a let's encrypt certificate", function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/https', [
        'json' => ['type' => 'webroot'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:https:enable 1 --type=letsencrypt')
        ->expectsOutput('==> HTTPS queued for enabling.')
        ->assertExitCode(0);
});

test("enable https asks for the certificate type and defaults to let's encrypt", function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/https', [
        'json' => ['type' => 'webroot'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:https:enable 1')
        ->expectsChoice('Which certificate would you like to use?', 'letsencrypt', ['letsencrypt' => "Let's Encrypt", 'custom' => 'Custom certificate'])
        ->expectsOutput('==> HTTPS queued for enabling.')
        ->assertExitCode(0);
});

test('enable https with a custom certificate', function () {
    $dir = sys_get_temp_dir();
    file_put_contents("{$dir}/spinupwp_test_cert.pem", "CERTIFICATE\n");
    file_put_contents("{$dir}/spinupwp_test_cert.key", "PRIVATE KEY\n");

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/https', [
        'json' => ['type' => 'custom', 'certificate' => 'CERTIFICATE', 'private_key' => 'PRIVATE KEY'],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    try {
        $this->artisan("sites:https:enable 1 --type=custom --certificate={$dir}/spinupwp_test_cert.pem --private-key={$dir}/spinupwp_test_cert.key")
            ->expectsOutput('==> HTTPS queued for enabling.')
            ->assertExitCode(0);
    } finally {
        unlink("{$dir}/spinupwp_test_cert.pem");
        unlink("{$dir}/spinupwp_test_cert.key");
    }
});

test('enable https with a custom certificate requires both files', function () {
    $this->artisan('sites:https:enable 1 --type=custom --certificate=/tmp/cert.pem --force')
        ->expectsOutput('A custom certificate needs both the --certificate and --private-key options.')
        ->assertExitCode(1);
});

test('enable https rejects an unknown certificate type', function () {
    $this->artisan('sites:https:enable 1 --type=self-signed')
        ->expectsOutput('The --type option must be letsencrypt or custom.')
        ->assertExitCode(1);
});
