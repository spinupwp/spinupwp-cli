<?php

use App\Repositories\ConfigRepository;

beforeEach(function () {
    deleteTestConfigFile();
});

afterEach(function () {
});

test('configure command for default profile', function () {
    $this->artisan('configure')
        ->expectsQuestion('SpinupWP API token', 'my-spinupwp-api-token')
        ->expectsQuestion('Default output format (json/table)', 'json')
        ->expectsOutput('Profile configured successfully.');

    expect((resolve(ConfigRepository::class))->get('api_token'))->toEqual('my-spinupwp-api-token');
});
