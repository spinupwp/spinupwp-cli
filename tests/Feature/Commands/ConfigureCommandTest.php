<?php

use App\Helpers\Configuration;

beforeEach(function () {
    setConfigPath();
    deleteTestConfigFile();
});

afterEach(function () {
});

test('configure command for default profile', function () {
    $this->artisan('configure')
        ->expectsQuestion('SpinupWP API token', 'my-spinupwp-api-token')
        ->expectsQuestion('Default output format (json/table)', 'json')
        ->expectsOutput('SpinupWP CLI configured successfully');

    expect((new Configuration)->get('api_token'))->toEqual('my-spinupwp-api-token');
});
