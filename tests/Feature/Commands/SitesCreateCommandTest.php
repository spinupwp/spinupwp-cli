<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->inputParamDefaults = [
        'installation_method' => null,
        'domain'              => null,
        'php_version'         => '8.3',
        'site_user'           => null,
        'page_cache'          => [
            'enabled' => 0,
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
        'site_user'           => '',
    ]);

    $this->clientMock->shouldReceive('request')->with('POST', 'sites', [
        'json' => $params,
    ])->andReturn(
        new Response(422, [], json_encode([
          'message' => 'The given data was invalid.',
          'errors'  => [
              ['field' => 'error message'],
          ],
      ]))
    );

    $this->artisan('sites:create 1 --installation-method=blank -f')
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
        'page_cache' => ['enabled' => true],
    ]);

    $this->clientMock->shouldReceive('request')->with('POST', 'sites', [
        'json' => $params,
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => '100',
        'data'     => [
            'id'     => 1,
            'domain' => 'hellfish.media',
            'status' => 'deploying',
        ],
    ])));

    $this->artisan('sites:create 1 --installation-method=blank --domain=hellfish.media --page-cache-enabled --php-version="7.4" -f')
        ->expectsOutput('==> Site queued for creation.')
        ->expectsOutputToContain('Site ID')
        ->expectsOutputToContain('hellfish.media')
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
        'json' => $params,
    ])->andReturn(
        new Response(422, [], json_encode([
            'message' => 'The given data was invalid.',
            'errors'  => [
                ['field' => 'error message'],
            ],
        ]))
    );

    $this->artisan('sites:create 1 --installation-method=wp --db-pass=password --wp-admin-pass=password -f')
        ->expectsOutput('Validation errors occurred.')
        ->assertExitCode(1);
});

test('"sites:create wp" succeeds with correct data', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'wp',
        'server_id'           => 1,
        'domain'              => 'hellfish.media',
        'site_user'           => 'test',
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
        'json' => $params,
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

    $this->artisan('sites:create 1
                            --installation-method=wp
                            --domain="hellfish.media"
                            --site-user=test
                            --db-name=dbname
                            --db-user=dbuser
                            --db-pass=password
                            --wp-title="Site Title"
                            --wp-admin-user="abe"
                            --wp-admin-email="flying@hellfish.media"
                            --wp-admin-pass=password -f')
        ->assertExitCode(0);
});

test('"sites:create wp_subdirectory" sends the table prefix and public folder', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'wp_subdirectory',
        'server_id'           => 1,
        'domain'              => 'hellfish.media',
        'site_user'           => 'test',
        'public_folder'       => '/public',
        'database'            => [
            'name'         => 'dbname',
            'username'     => 'dbuser',
            'password'     => 'password',
            'table_prefix' => 'hf_',
        ],
        'wordpress' => [
            'title'          => 'Site Title',
            'admin_user'     => 'abe',
            'admin_email'    => 'flying@hellfish.media',
            'admin_password' => 'password',
        ],
    ]);

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites', [
        'json' => $params,
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => '100',
        'data'     => ['id' => 1, 'domain' => 'hellfish.media', 'status' => 'deploying'],
    ])));

    $this->artisan('sites:create 1 --installation-method=wp_subdirectory --domain=hellfish.media --site-user=test --public-folder=/public --db-name=dbname --db-user=dbuser --db-pass=password --db-table-prefix=hf_ --wp-title="Site Title" --wp-admin-user=abe --wp-admin-email=flying@hellfish.media --wp-admin-pass=password -f')
        ->expectsOutput('==> Site queued for creation.')
        ->assertExitCode(0);
});

test('"sites:create git" sends the git settings and deploy key', function () {
    $dir = sys_get_temp_dir();
    file_put_contents("{$dir}/spinupwp_create_key", "PRIVATE\n");
    file_put_contents("{$dir}/spinupwp_create_key.pub", "PUBLIC\n");

    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'git',
        'server_id'           => 1,
        'domain'              => 'hellfish.media',
        'site_user'           => 'hellfishmedia',
        'public_folder'       => '/public',
        'deploy_script'       => 'composer install --no-dev',
        'git'                 => [
            'repo'                     => 'git@github.com:spinupwp/spinupwp-composer-site.git',
            'branch'                   => 'production',
            'push_to_deploy'           => true,
            'always_run_deploy_script' => false,
            'deploy_key_enabled'       => true,
            'deploy_key'               => [
                'privatekey' => 'PRIVATE',
                'publickey'  => 'PUBLIC',
            ],
        ],
    ]);

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites', [
        'json' => $params,
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => '100',
        'data'     => ['id' => 1, 'domain' => 'hellfish.media', 'status' => 'deploying'],
    ])));

    try {
        $this->artisan("sites:create 1 --installation-method=git --domain=hellfish.media --git-repo=git@github.com:spinupwp/spinupwp-composer-site.git --git-branch=production --public-folder=/public --push-to-deploy --deploy-script=\"composer install --no-dev\" --git-private-key={$dir}/spinupwp_create_key --git-public-key={$dir}/spinupwp_create_key.pub -f")
            ->expectsOutput('==> Site queued for creation.')
            ->assertExitCode(0);
    } finally {
        unlink("{$dir}/spinupwp_create_key");
        unlink("{$dir}/spinupwp_create_key.pub");
    }
});

test('"sites:create git" asks for the git settings interactively', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'git',
        'server_id'           => 1,
        'domain'              => 'hellfish.media',
        'site_user'           => 'hellfishmedia',
        'php_version'         => '8.3',
        'public_folder'       => '/',
        'git'                 => [
            'repo'                     => 'git@github.com:spinupwp/spinupwp-composer-site.git',
            'branch'                   => 'main',
            'push_to_deploy'           => false,
            'always_run_deploy_script' => false,
            'deploy_key_enabled'       => false,
        ],
    ]);

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites', [
        'json' => $params,
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => '100',
        'data'     => ['id' => 1, 'domain' => 'hellfish.media', 'status' => 'deploying'],
    ])));

    $this->artisan('sites:create 1 --installation-method=git')
        ->expectsConfirmation('Are you sure you want to deploy to "hellfish-media"?', 'yes')
        ->expectsQuestion('Primary Domain', 'hellfish.media')
        ->expectsQuestion('Site User', 'hellfishmedia')
        ->expectsQuestion('Git Repository', 'git@github.com:spinupwp/spinupwp-composer-site.git')
        ->expectsQuestion('Git Branch', 'main')
        ->expectsQuestion('Public Folder', '/')
        ->expectsConfirmation('Enable Push to Deploy', 'no')
        ->expectsChoice('PHP Version', '8.3', ['8.5', '8.4', '8.3', '8.2', '8.1', '8.0', '7.4'])
        ->expectsConfirmation('Enable Page Cache', 'no')
        ->expectsOutput('==> Site queued for creation.')
        ->assertExitCode(0);
});

test('"sites:create git" sends a database only when a database name is given', function () {
    $params = array_merge($this->inputParamDefaults, [
        'installation_method' => 'git',
        'server_id'           => 1,
        'domain'              => 'hellfish.media',
        'site_user'           => 'hellfishmedia',
        'public_folder'       => '/',
        'database'            => [
            'name'     => 'hellfish',
            'username' => 'hellfish',
            'password' => 'password',
        ],
        'git' => [
            'repo'                     => 'git@github.com:spinupwp/spinupwp-composer-site.git',
            'branch'                   => 'main',
            'push_to_deploy'           => false,
            'always_run_deploy_script' => false,
            'deploy_key_enabled'       => false,
        ],
    ]);

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites', [
        'json' => $params,
    ])->andReturn(new Response(200, [], json_encode([
        'event_id' => '100',
        'data'     => ['id' => 1, 'domain' => 'hellfish.media', 'status' => 'deploying'],
    ])));

    $this->artisan('sites:create 1 --installation-method=git --domain=hellfish.media --git-repo=git@github.com:spinupwp/spinupwp-composer-site.git --db-name=hellfish --db-user=hellfish --db-pass=password -f')
        ->expectsOutput('==> Site queued for creation.')
        ->assertExitCode(0);
});

test('"sites:create git" shows a validation message that has no field errors', function () {
    $dir = sys_get_temp_dir();
    file_put_contents("{$dir}/spinupwp_create_key", "PRIVATE\n");
    file_put_contents("{$dir}/spinupwp_create_key.pub", "NOT A PUBLIC KEY\n");

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites', Mockery::any())->andReturn(
        new Response(422, [], json_encode(['message' => 'The git deploy key is not valid.']))
    );

    try {
        $this->artisan("sites:create 1 --installation-method=git --domain=hellfish.media --git-repo=git@github.com:org/repo.git --git-private-key={$dir}/spinupwp_create_key --git-public-key={$dir}/spinupwp_create_key.pub -f")
            ->expectsOutput('The git deploy key is not valid.')
            ->assertExitCode(1);
    } finally {
        unlink("{$dir}/spinupwp_create_key");
        unlink("{$dir}/spinupwp_create_key.pub");
    }
});

test('"sites:create git" requires both deploy key files', function () {
    $this->artisan('sites:create 1 --installation-method=git --domain=hellfish.media --git-repo=git@github.com:org/repo.git --git-private-key=/tmp/key -f')
        ->expectsOutput('The --git-private-key and --git-public-key options must be provided together.')
        ->assertExitCode(1);
});

test('"sites:create" no longer accepts the https option', function () {
    expect(fn () => $this->artisan('sites:create 1 --installation-method=blank --domain=hellfish.media --https-enabled -f')->run())
        ->toThrow(Symfony\Component\Console\Exception\RuntimeException::class, 'The "--https-enabled" option does not exist.');
});
