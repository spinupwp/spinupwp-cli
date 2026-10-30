<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->serverResponse = new Response(200, [], json_encode([
        'event_id' => 100,
        'data'     => ['id' => 7, 'name' => 'hellfish-media', 'status' => 'provisioning'],
    ]));
});

afterEach(function () {
    deleteTestConfigFile();
});

test('provision a server with a saved provider account', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers', [
        'json' => [
            'server_provider' => ['id' => 3, 'region' => 'nyc3', 'size' => 's-1vcpu-2gb', 'enable_backups' => false],
            'hostname'        => 'hellfish-media',
            'timezone'        => 'UTC',
        ],
    ])->andReturn($this->serverResponse);

    $this->artisan('servers:create --provider-id=3 --region=nyc3 --size=s-1vcpu-2gb --hostname=hellfish-media --force')
        ->expectsOutput('==> Server queued for provisioning.')
        ->assertExitCode(0);
});

test('provision a server with a saved provider account names the account, not --provider', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers', [
        'json' => [
            'server_provider' => ['id' => 3, 'region' => 'nyc3', 'size' => 's-1vcpu-2gb', 'enable_backups' => false],
            'hostname'        => 'hellfish-media',
            'timezone'        => 'UTC',
        ],
    ])->andReturn($this->serverResponse);

    $this->artisan('servers:create --provider=hetzner --provider-id=3 --region=nyc3 --size=s-1vcpu-2gb --hostname=hellfish-media --timezone=UTC')
        ->expectsConfirmation('Your server provider charges extra for backups. Enable them?', 'no')
        ->expectsConfirmation('Provision a s-1vcpu-2gb server in nyc3 with saved provider account 3? Your server provider will bill you for it.', 'yes')
        ->expectsOutput('==> Server queued for provisioning.')
        ->assertExitCode(0);
});

test('provision a server with an api token and the optional settings', function () {
    $script = sys_get_temp_dir() . '/spinupwp_test_provision.sh';
    file_put_contents($script, "#!/bin/bash\necho done\n");

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers', [
        'json' => [
            'server_provider'       => ['name' => 'hetzner', 'api_token' => 'secret-token', 'region' => 'fsn1', 'size' => 'cx22', 'enable_backups' => true],
            'hostname'              => 'hellfish-media',
            'timezone'              => 'Europe/Berlin',
            'database'              => ['root_password' => 'rootpassword'],
            'database_provider'     => ['id' => 4],
            'post_provision_script' => "#!/bin/bash\necho done",
        ],
    ])->andReturn($this->serverResponse);

    try {
        $this->artisan("servers:create --provider=hetzner --api-token=secret-token --region=fsn1 --size=cx22 --enable-backups --hostname=hellfish-media --timezone=Europe/Berlin --database-root-password=rootpassword --database-provider-id=4 --post-provision-script={$script} --force")
            ->expectsOutput('==> Server queued for provisioning.')
            ->assertExitCode(0);
    } finally {
        unlink($script);
    }
});

test('provision a server interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'providers/digitalocean/metadata', [])->andReturn(
        new Response(200, [], json_encode([
            'regions' => [
                'Americas' => [
                    ['slug' => 'nyc3', 'name' => 'New York 3', 'available' => true, 'sizes' => ['s-1vcpu-2gb']],
                    ['slug' => 'sfo1', 'name' => 'San Francisco 1', 'available' => false, 'sizes' => []],
                ],
            ],
            'sizes' => [
                ['slug' => 's-1vcpu-2gb', 'memory' => 2048, 'vcpus' => 1, 'disk' => 50, 'transfer' => 2, 'priceMonthly' => 12, 'available' => true],
                ['slug' => 's-2vcpu-4gb', 'memory' => 4096, 'vcpus' => 2, 'disk' => 80, 'transfer' => 4, 'priceMonthly' => 24, 'available' => true],
            ],
        ]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers', [
        'json' => [
            'server_provider' => ['name' => 'digitalocean', 'api_token' => 'secret-token', 'region' => 'nyc3', 'size' => 's-1vcpu-2gb', 'enable_backups' => false],
            'hostname'        => 'hellfish-media',
            'timezone'        => 'UTC',
        ],
    ])->andReturn($this->serverResponse);

    $this->artisan('servers:create')
        ->expectsChoice('Which server provider', 'digitalocean', ['digitalocean' => 'DigitalOcean', 'hetzner' => 'Hetzner', 'linode' => 'Linode', 'vultr' => 'Vultr'])
        ->expectsQuestion('DigitalOcean API token (or pass --provider-id to use a provider account saved in SpinupWP)', 'secret-token')
        ->expectsChoice('Which region', 'nyc3', ['nyc3' => 'New York 3 (Americas)'])
        ->expectsChoice('Which size', 's-1vcpu-2gb', ['s-1vcpu-2gb' => '1 vCPUs, 2 GB memory, 50 GB disk, $12.00/month'])
        ->expectsQuestion('Hostname', 'hellfish-media')
        ->expectsQuestion('Timezone', 'UTC')
        ->expectsConfirmation('DigitalOcean charges extra for backups. Enable them?', 'no')
        ->expectsConfirmation('Provision a s-1vcpu-2gb server in nyc3 on DigitalOcean? DigitalOcean will bill you for it.', 'yes')
        ->expectsOutput('==> Server queued for provisioning.')
        ->assertExitCode(0);
});

test('provision a server with force option requires the provider', function () {
    $this->artisan('servers:create --region=nyc3 --size=s-1vcpu-2gb --force')
        ->expectsOutput('The --provider option is required when running with --force, unless you pass --provider-id.')
        ->assertExitCode(1);
});

test('provision a server with force option requires credentials', function () {
    $this->artisan('servers:create --provider=vultr --region=ewr --size=vc2-1c-2gb --force')
        ->expectsOutput('Pass --provider-id to use a provider account saved in SpinupWP, or --api-token.')
        ->assertExitCode(1);
});

test('provision a server shows validation errors', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers', Mockery::any())->andReturn(
        new Response(422, [], json_encode([
            'message' => 'The given data was invalid.',
            'errors'  => ['server_provider.region' => ['Invalid DigitalOcean region.']],
        ]))
    );

    $this->artisan('servers:create --provider-id=3 --region=mars1 --size=s-1vcpu-2gb --hostname=hellfish-media --force')
        ->expectsOutput('Validation errors occurred.')
        ->assertExitCode(1);
});

test('provision a server with force option requires the region and size', function () {
    $this->artisan('servers:create --provider-id=3 --hostname=hellfish-media --force')
        ->expectsOutput('The --region and --size options are required when running with --force.')
        ->assertExitCode(1);
});

test('provision a server with a saved provider account, choosing its region and size interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'providers/hetzner/metadata', [])->andReturn(
        new Response(200, [], json_encode([
            'regions' => ['Americas' => [['slug' => 'ash', 'name' => 'Ashburn, VA, US', 'available' => true, 'sizes' => ['cpx11']]]],
            'sizes'   => [['slug' => 'cpx11', 'memory' => 2048, 'vcpus' => 2, 'disk' => 40, 'transfer' => 1, 'priceMonthly' => 17.99, 'available' => true]],
        ]))
    );

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers', [
        'json' => [
            'server_provider' => ['id' => 1, 'region' => 'ash', 'size' => 'cpx11', 'enable_backups' => false],
            'hostname'        => 'hellfish-media',
            'timezone'        => 'UTC',
        ],
    ])->andReturn($this->serverResponse);

    $this->artisan('servers:create --provider-id=1')
        ->expectsChoice('Which provider is saved provider account 1 on? (used to list its regions and sizes)', 'hetzner', ['digitalocean' => 'DigitalOcean', 'hetzner' => 'Hetzner', 'linode' => 'Linode', 'vultr' => 'Vultr'])
        ->expectsChoice('Which region', 'ash', ['ash' => 'Ashburn, VA, US (Americas)'])
        ->expectsChoice('Which size', 'cpx11', ['cpx11' => '2 vCPUs, 2 GB memory, 40 GB disk, $17.99/month'])
        ->expectsQuestion('Hostname', 'hellfish-media')
        ->expectsQuestion('Timezone', 'UTC')
        ->expectsConfirmation('Hetzner charges extra for backups. Enable them?', 'no')
        ->expectsConfirmation('Provision a cpx11 server in ash with saved provider account 1? Hetzner will bill you for it.', 'yes')
        ->expectsOutput('==> Server queued for provisioning.')
        ->assertExitCode(0);
});
