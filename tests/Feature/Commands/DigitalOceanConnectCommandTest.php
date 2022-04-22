<?php

beforeEach(function () {
    setTestConfigFile();
    setTestMysqlPwdFile();
});

afterEach(function () {
    deleteTestConfigFile();
    deleteTestMysqlPwdFile();
});

test('digitalocean connect command', function () {
    $this->artisan('digitalocean:connect')
        ->expectsOutput('Welcome to the SpinupWP 1-click app. This wizard will allow you to connect your brand new server to your SpinupWP account')
        ->expectsQuestion('Do you want to continue?', 'yes')
        ->expectsOutput('Database Root Password')
        ->expectsQuestion('Do you want to generate a random password?', false)
        ->expectsQuestion('Enter a new MySQL root password', '123')
        ->expectsOutput('Your MySQL root password: 123')
        ->expectsOutput("Make sure to note this password down somewhere (or save it in a Password Manager) as we won't show it again.")
        ->expectsQuestion('Press Enter to continue', true)
        ->expectsOutput('Changing MySQL root password')
        ->expectsOutput('MySQL root password changed.')
        ->expectsOutput('Connecting to spinupwp.app')
        ->expectsOutput('To connect your server to your SpinupWP account, please visit https://spinupwp.app/connect-image/abc-123 and cconfirm your server connection. Please Enter when done');
});
