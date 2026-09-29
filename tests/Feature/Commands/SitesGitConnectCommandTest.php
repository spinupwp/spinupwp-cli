<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 1, 'domain' => 'hellfishmedia.com', 'git' => ['enabled' => false]]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('connect git with options', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/git', [
        'json' => [
            'repo'           => 'git@github.com:spinupwp/spinupwp-composer-site.git',
            'branch'         => 'main',
            'deploy_script'  => 'composer install',
            'push_to_deploy' => true,
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:git:connect 1 --repo=git@github.com:spinupwp/spinupwp-composer-site.git --branch=main --deploy-script="composer install" --push-to-deploy')
        ->expectsOutput('==> Git repository queued for connection.')
        ->assertExitCode(0);
});

test('connect git interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/git', [
        'json' => [
            'repo'           => 'git@github.com:spinupwp/spinupwp-composer-site.git',
            'branch'         => 'develop',
            'push_to_deploy' => false,
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:git:connect 1')
        ->expectsQuestion('Repository', 'git@github.com:spinupwp/spinupwp-composer-site.git')
        ->expectsQuestion('Branch', 'develop')
        ->expectsOutput('==> Git repository queued for connection.')
        ->assertExitCode(0);
});

test('connect git with a deploy key', function () {
    $dir = sys_get_temp_dir();
    file_put_contents("{$dir}/spinupwp_test_key", "PRIVATE\n");
    file_put_contents("{$dir}/spinupwp_test_key.pub", "PUBLIC\n");

    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/git', [
        'json' => [
            'repo'           => 'repo',
            'branch'         => 'main',
            'push_to_deploy' => false,
            'deploy_key'     => [
                'privatekey' => 'PRIVATE',
                'publickey'  => 'PUBLIC',
            ],
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan("sites:git:connect 1 --repo=repo --branch=main --private-key={$dir}/spinupwp_test_key --public-key={$dir}/spinupwp_test_key.pub")
        ->expectsOutput('==> Git repository queued for connection.')
        ->assertExitCode(0);

    unlink("{$dir}/spinupwp_test_key");
    unlink("{$dir}/spinupwp_test_key.pub");
});

test('connect git requires both deploy key files', function () {
    $this->artisan('sites:git:connect 1 --repo=repo --branch=main --private-key=/tmp/key')
        ->expectsOutput('The --private-key and --public-key options must be provided together.')
        ->assertExitCode(1);
});

test('connect git with force option uses the default branch', function () {
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/git', [
        'json' => [
            'repo'           => 'git@github.com:spinupwp/spinupwp-composer-site.git',
            'branch'         => 'main',
            'push_to_deploy' => false,
        ],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:git:connect 1 --repo=git@github.com:spinupwp/spinupwp-composer-site.git --force')
        ->expectsOutput('==> Git repository queued for connection.')
        ->assertExitCode(0);
});
