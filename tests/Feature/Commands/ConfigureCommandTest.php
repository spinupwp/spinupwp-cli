<?php

use App\Helpers\Configuration;

test('configure command for default profile', function () {
    setConfigPath();

    $this->artisan('configure')
        ->expectsQuestion('Enter your API token for the default team. You can get from your SpinupWP account page https://spinupwp.app', 'my-spinupwp-api-token')
        ->expectsQuestion('Which format would you prefer for data output? (json/table)', 'json')
        ->expectsOutput('SpinupWP CLI configured successfully');

    expect((new Configuration)->get('api_token'))->toEqual('my-spinupwp-api-token');
});
