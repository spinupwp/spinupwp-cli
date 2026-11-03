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
        ->expectsChoice('Which certificate would you like to use?', 'letsencrypt', ['letsencrypt' => "Let's Encrypt", 'dns' => "Let's Encrypt, validated through a DNS provider", 'custom' => 'Custom certificate'])
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
        ->expectsOutput('The --type option must be letsencrypt, dns or custom.')
        ->assertExitCode(1);
});

test('enable https with a dns certificate from the given DNS provider', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/https', [
        'json' => ['type' => 'dns', 'dns_provider_id' => 12],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:https:enable 1 --type=dns --dns-provider-id=12')
        ->expectsOutput('==> HTTPS queued for enabling.')
        ->assertExitCode(0);
});

test('enable https with a dns certificate asks which DNS provider to use', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'dns-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 12, 'service' => 'cloudflare', 'name' => 'Cloudflare'],
            ['id' => 15, 'service' => 'route53', 'name' => 'Client AWS'],
        ]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/https', [
        'json' => ['type' => 'dns', 'dns_provider_id' => 15],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:https:enable 1 --type=dns')
        ->expectsChoice('Which DNS provider should validate the certificate?', '15', [12, 'Cloudflare (cloudflare)', 15, 'Client AWS (route53)'])
        ->expectsOutput('==> HTTPS queued for enabling.')
        ->assertExitCode(0);
});

test('enable https with a dns certificate needs a DNS provider when not prompting', function () {
    $this->artisan('sites:https:enable 1 --type=dns --force')
        ->expectsOutput('A DNS validated certificate needs the --dns-provider-id option.')
        ->assertExitCode(1);
});

test('enable https with a dns certificate when the team has no DNS providers', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'dns-providers?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([]))
    );

    $this->artisan('sites:https:enable 1 --type=dns')
        ->expectsOutput('No DNS providers found. Add one in your SpinupWP team settings first.')
        ->assertExitCode(1);
});
