<?php

use App\Helpers\Configuration;
use DeliciousBrains\SpinupWp\SpinupWp;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Testing\TestCase;
use Tests\CreatesApplication;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class, CreatesApplication::class)
    ->beforeEach(function () {
        $this->clientMock = Mockery::mock(Client::class);
        $this->spinupwp   = resolve(SpinupWp::class)->setClient($this->clientMock)->setApiKey('123');
        config()->set('app.ssh_timeout', -1);
    })
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function setTestConfigFile($profileData = [])
{
    $config = resolve(Configuration::class);
    file_put_contents($config->configFilePath(), json_encode([
        'default' => array_merge([
            'api_token' => 'myapikey123',
            'format'    => 'json',
        ], $profileData),
    ], JSON_PRETTY_PRINT));
}

function deleteTestConfigFile($test = '')
{
    $configFile = (resolve(Configuration::class))->configFilePath();
    if (!file_exists($configFile)) {
        return;
    }
    unlink($configFile);
}
