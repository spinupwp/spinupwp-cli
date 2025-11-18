<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();
});

test('"servers:create-custom" fails with invalid data', function () {
    $params = [
        'provider_name'          => 'test',
        'ubuntu_version'         => '24.04',
        'ip_address'             => null,
        'ssh_port'               => '22',
        'username'               => null,
        'auth_method'            => 'publickey',
        'password'               => null,
        'hostname'               => null,
        'timezone'               => 'UTC',
        'post_provision_script'  => null,
        'database_root_password' => null,
        'database_provider_id'   => null,
];

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/custom', [
        'form_params' => $params,
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
        'provider_name'            => 'my provider',
          'ubuntu_version'         => '24.04',
          'ip_address'             => '127.0.0.1',
          'ssh_port'               => '22',
          'username'               => 'root',
          'auth_method'            => 'publickey',
          'password'               => null,
          'hostname'               => 'myserver.com',
          'timezone'               => 'UTC',
          'post_provision_script'  => null,
          'database_root_password' => null,
          'database_provider_id'   => null,
    ];

    $this->clientMock->shouldReceive('request')->with('POST', 'servers/custom', [
        'form_params' => $params,
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
