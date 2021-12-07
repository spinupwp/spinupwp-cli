<?php

use App\Helpers\Configuration;

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

    expect((resolve(Configuration::class))->get('api_token'))->toEqual('my-spinupwp-api-token');
});
