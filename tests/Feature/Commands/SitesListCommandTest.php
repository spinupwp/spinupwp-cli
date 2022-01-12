<?php

use App\Helpers\Configuration;
use GuzzleHttp\Psr7\Response;

$response = [
    [
        'id'          => 1,
        'server_id'   => 1,
        'domain'      => 'hellfishmedia.com',
        'site_user'   => 'hellfish',
        'php_version' => '8.0',
        'page_cache'  => [
            'enabled' => true,
        ],
        'https' => [
            'enabled' => true,
        ],
    ],
    [
        'id'          => 2,
        'server_id'   => 2,
        'domain'      => 'staging.hellfishmedia.com',
        'site_user'   => 'staging-hellfish',
        'php_version' => '8.0',
        'page_cache'  => [
            'enabled' => false,
        ],
        'https' => [
            'enabled' => false,
        ],
    ],
];
beforeEach(function () use ($response) {
    setTestConfigFile();
});

afterEach(function () {
    deleteTestConfigFile();
});

it('list command with no api token configured', function () {
    $this->spinupwp->setApiKey('');
    $this->artisan('sites:list --profile=johndoe')
        ->assertExitCode(1);
});

test('sites json list command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );
    $this->artisan('sites:list')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('sites table list command', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );
    $this->artisan('sites:list --format table')->expectsTable(
        ['ID', 'Server ID', 'Domain', 'Site User', 'PHP Version', 'Page Cache', 'HTTPS'],
        [
            [
                1,
                1,
                'hellfishmedia.com',
                'hellfish',
                '8.0',
                'Enabled',
                'Enabled',
            ],
            [
                2,
                2,
                'staging.hellfishmedia.com',
                'staging-hellfish',
                '8.0',
                'Disabled',
                'Disabled',
            ],
        ]
    );
});

test('sites table list with specified columns command and asks to save it in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );
    $this->artisan('sites:list --format table --fields=id,domain,site_user')
        ->expectsConfirmation('Do you want to save the specified fields as the default for this command?', 'yes')
        ->expectsTable(
            ['ID', 'Domain', 'Site User'],
            [
            [
                1,
                'hellfishmedia.com',
                'hellfish',
            ],
            [
                2,
                'staging.hellfishmedia.com',
                'staging-hellfish',
            ],
        ]
        );

    $this->assertEquals('id,domain,site_user', resolve(Configuration::class)->getCommandConfiguration('sites:list')['fields']);
});

test('sites table list only columns saved in the config', function () use ($response) {
    $this->clientMock->shouldReceive('request')->once()->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson($response))
    );

    resolve(Configuration::class)->setCommandConfiguration('sites:list', 'fields', 'id,domain');

    $this->artisan('sites:list --format=table')->expectsTable(
        ['ID', 'Domain'],
        [
            [
                1,
                'hellfishmedia.com',
            ],
            [
                2,
                'staging.hellfishmedia.com',
            ],
        ]
    );
});

test('empty sites list', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], listResponseJson([]))
    );
    $this->artisan('sites:list')->expectsOutput('No sites found.');
});
