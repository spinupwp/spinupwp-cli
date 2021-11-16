<?php

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
    $this->clientMock->shouldReceive('request')->with('GET', 'sites?page=1', [])->andReturn(
        new Response(200, [], json_encode([
            'data' => $response,
        ]))
    );
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
    $this->artisan('sites:list')->expectsOutput(json_encode($response, JSON_PRETTY_PRINT));
});

test('sites table list command', function () {
    $this->artisan('sites:list --format table')->expectsTable(
        ['ID', 'Server ID', 'Domain', 'Site User', 'PHP', 'Page Cache', 'HTTPS'],
        [
            [
                1,
                1,
                'hellfishmedia.com',
                'hellfish',
                '8.0',
                'Y',
                'Y',
            ],
            [
                2,
                2,
                'staging.hellfishmedia.com',
                'staging-hellfish',
                '8.0',
                'N',
                'N',
            ],
        ]
    );
});
