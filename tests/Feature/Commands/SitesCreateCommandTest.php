<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->inputParamDefaults = [
        'installation_method' => null,
        'domain'              => null,
        'php_version'         => '8.0',
        'site_user'           => null,
        'page_cache'          => [
            'enabled' => 0,
        ],
        'https' => [
            'enabled' => 0,
        ],
        'database' => [
            'name'     => null,
            'username' => null,
            'password' => null,
        ],
        'wordpress' => [
            'title'          => null,
            'admin_user'     => null,
            'admin_email'    => null,
            'admin_password' => null,
        ],
    ];

    $this->clientMock->shouldReceive('request')->with('GET', 'servers/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'name' => 'hellfish-media']]))
    );
});

test('"sites:create blank" fails with invalid data', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'blank',
        'server_id'           => 1,
    ]);

    $this->clientMock->shouldReceive('request')->with('POST', 'sites', [
        'form_params' => $params,
    ])->andReturn(
        new Response(422, [], json_encode([
          'message' => 'The given data was invalid.',
          'errors'  => [
              ['field' => 'error message'],
          ],
      ]))
    );

    $this->artisan('sites:create blank 1 -f')
        ->expectsOutput('Validation errors occurred.')
        ->assertExitCode(1);
});

test('"sites:create blank" succeeds with correct params', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'blank',
        'domain'              => 'hellfish.media',
        'server_id'           => 1,
        'php_version'         => '7.4',

        'site_user'  => 'hellfishmedia',
        'https'      => ['enabled' => true],
        'page_cache' => ['enabled' => true],
    ]);

    $this->clientMock->shouldReceive('request')->with('POST', 'sites', [
        'form_params' => $params,
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => '100',
        'data'     => [
            'id'     => 1,
            'domain' => 'hellfish.media',
            'status' => 'deploying',
        ],
    ])));

    $this->artisan('sites:create blank 1 --domain=hellfish.media --https_enabled --page_cache_enabled --php_version="74" -f')
        ->assertExitCode(0);
});

test('"sites:create wp" fails with invalid data', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'wp',
        'server_id'           => 1,
        'site_user'           => '',
        'database'            => [
            'name'     => '',
            'username' => '',
            'password' => 'password',
        ],
        'wordpress' => [
            'title'          => null,
            'admin_user'     => null,
            'admin_email'    => null,
            'admin_password' => 'password',
        ],
    ]);

    $this->clientMock->shouldReceive('request')->with('POST', 'sites', [
        'form_params' => $params,
    ])->andReturn(
        new Response(422, [], json_encode([
            'message' => 'The given data was invalid.',
            'errors'  => [
                ['field' => 'error message'],
            ],
        ]))
    );

    $this->artisan('sites:create wp 1 --db_pass=password --wp_admin_pass=password -f')
        ->expectsOutput('Validation errors occurred.')
        ->assertExitCode(1);
});

test('"sites:create wp" succeeds with correct data', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'wp',
        'server_id'           => 1,
        'domain'              => 'hellfish.media',
        'site_user'           => 'test',
        'https'               => ['enabled' => true],
        'database'            => [
            'name'     => 'dbname',
            'username' => 'dbuser',
            'password' => 'password',
        ],
        'wordpress' => [
            'title'          => 'Site Title',
            'admin_user'     => 'abe',
            'admin_email'    => 'flying@hellfish.media',
            'admin_password' => 'password',
        ],
    ]);

    $this->clientMock->shouldReceive('request')->with('POST', 'sites', [
        'form_params' => $params,
    ])->andReturn(
        new Response(200, [], json_encode([
            'event_id' => '100',
            'data'     => [
                'id'     => 1,
                'domain' => 'hellfish.media',
                'status' => 'deploying',
            ],
        ]))
    );

    $this->artisan('sites:create wp 1
                            --domain="hellfish.media"
                            --site_user=test
                            --https_enabled
                            --db_name=dbname
                            --db_user=dbuser
                            --db_pass=password
                            --wp_title="Site Title"
                            --wp_admin_user="abe"
                            --wp_admin_email="flying@hellfish.media"
                            --wp_admin_pass=password -f')
        ->assertExitCode(0);
});
