<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

test('"servers:create-custom" fails with invalid data', function () {
    $params = [
        'provider_name'  => 'test',
        'ubuntu_version' => '26.04',
        'ip_address'     => null,
        'ssh_port'       => '22',
        'username'       => null,
        'auth_method'    => 'publickey',
        'hostname'       => null,
        'timezone'       => 'UTC',
];

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/custom', [
        'json' => $params,
    ])->andReturn(
        new Response(422, [], json_encode([
            'message' => 'The given data was invalid.',
            'errors'  => [
                ['field' => 'error message'],
            ],
        ]))
    );

    $this->artisan('servers:create-custom --provider-name=test -f')
        ->expectsOutput('Validation errors occurred.')
        ->assertExitCode(1);
});

test('"servers:create-custom" succeeds with correct data', function () {
    $params = [
        'provider_name'    => 'my provider',
          'ubuntu_version' => '24.04',
          'ip_address'     => '127.0.0.1',
          'ssh_port'       => '22',
          'username'       => 'root',
          'auth_method'    => 'publickey',
          'hostname'       => 'myserver.com',
          'timezone'       => 'UTC',
    ];

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/custom', [
        'json' => $params,
    ])->andReturn(
        new Response(200, [], json_encode([
            'event_id' => '100',
            'data'     => [
                'id' => 1,
            ],
        ]))
    );

    $this->artisan('servers:create-custom
                            --provider-name="my provider"
                            --ubuntu-version=24.04
                            --ip-address=127.0.0.1
                            --ssh-port=22
                            --ssh-username=root
                            --ssh-auth-method=publickey
                            --hostname=myserver.com -f')
        ->assertExitCode(0);
});

test('"servers:create-custom" sends the contents of the post-provision script file', function () {
    $script = sys_get_temp_dir() . '/spinupwp_test_custom_provision.sh';
    file_put_contents($script, "#!/bin/bash\napt-get install -y htop\n");

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers/custom', [
        'json' => [
            'provider_name'         => 'my provider',
            'ubuntu_version'        => '24.04',
            'ip_address'            => '127.0.0.1',
            'ssh_port'              => '22',
            'username'              => 'root',
            'auth_method'           => 'publickey',
            'hostname'              => 'myserver.com',
            'timezone'              => 'UTC',
            'post_provision_script' => "#!/bin/bash\napt-get install -y htop",
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => '100', 'data' => ['id' => 1]])));

    try {
        $this->artisan("servers:create-custom --provider-name=\"my provider\" --ubuntu-version=24.04 --ip-address=127.0.0.1 --ssh-port=22 --ssh-username=root --ssh-auth-method=publickey --hostname=myserver.com --post-provision-script={$script} -f")
            ->assertExitCode(0);
    } finally {
        unlink($script);
    }
});

test('"servers:create-custom" fails when the post-provision script file is missing', function () {
    $this->artisan('servers:create-custom --provider-name=test --post-provision-script=/nonexistent/provision.sh -f')
        ->expectsOutput('Unable to read the file "/nonexistent/provision.sh".')
        ->assertExitCode(1);
});

test('"servers:create-custom" sends the password and database provider only when used', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers/custom', [
        'json' => [
            'provider_name'          => 'my provider',
            'ubuntu_version'         => '26.04',
            'ip_address'             => '127.0.0.1',
            'ssh_port'               => '2222',
            'username'               => 'root',
            'auth_method'            => 'password',
            'password'               => 'sshpassword',
            'hostname'               => 'myserver.com',
            'timezone'               => 'UTC',
            'database_root_password' => 'rootpassword',
            'database_provider_id'   => 4,
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => '100', 'data' => ['id' => 1]])));

    $this->artisan('servers:create-custom --provider-name="my provider" --ubuntu-version=26.04 --ip-address=127.0.0.1 --ssh-port=2222 --ssh-username=root --ssh-auth-method=password --ssh-password=sshpassword --hostname=myserver.com --database-root-password=rootpassword --database-provider-id=4 -f')
        ->assertExitCode(0);
});

test('"servers:create-custom" labels validation errors with capitalised acronyms', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'servers/custom', Mockery::any())->andReturn(
        new Response(422, [], json_encode([
            'message' => 'The given data was invalid.',
            'errors'  => [
                'ip_address'           => ['The ip_address must be a valid IP address.'],
                'database_provider_id' => ['The selected database_provider_id is invalid.'],
                'username'             => ['The username field is required.'],
            ],
        ]))
    );

    $this->artisan('servers:create-custom --provider-name=test -f')
        ->expectsOutput('Validation errors occurred.')
        ->expectsOutputToContain('IP Address')
        ->expectsOutputToContain('Database Provider ID')
        ->expectsOutputToContain('SSH Username')
        ->assertExitCode(1);
});
