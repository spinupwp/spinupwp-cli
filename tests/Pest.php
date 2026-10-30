<?php

use App\Repositories\ConfigRepository;
use App\Repositories\SpinupWpRepository;
use GuzzleHttp\Client;
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
        failOnAppDeprecations();

        $this->clientMock = Mockery::mock(Client::class);
        $this->spinupwp   = resolve(SpinupWpRepository::class)->setClient($this->clientMock)->setApiKey('123');
        config()->set('app.ssh_timeout', -1);
    })
    ->afterEach(function () {
        restore_error_handler();
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

/**
 * Laravel's error handler logs deprecations instead of reporting them to PHPUnit, so throw
 * for any deprecation raised from the app's own code. Deprecations from vendor code are
 * passed on to the previous handler.
 */
function failOnAppDeprecations(): void
{
    $appPath  = base_path('app') . DIRECTORY_SEPARATOR;
    $previous = null;

    $previous = set_error_handler(function (int $level, string $message, string $file = '', int $line = 0) use ($appPath, &$previous) {
        if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED], true) && str_starts_with($file, $appPath)) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }

        return is_callable($previous) ? $previous($level, $message, $file, $line) : false;
    });
}

function setTestConfigFile($profileData = [])
{
    $config = resolve(ConfigRepository::class);
    file_put_contents($config->configFilePath(), json_encode([
        'default' => array_merge([
            'api_token' => 'myapikey123',
            'format'    => 'json',
        ], $profileData),
    ], JSON_PRETTY_PRINT));
}

function deleteTestConfigFile($test = '')
{
    $configFile = (resolve(ConfigRepository::class))->configFilePath();
    if (!file_exists($configFile)) {
        return;
    }
    unlink($configFile);
}

function listResponseJson(array $data): string
{
    return json_encode([
        'data'       => $data,
        'pagination' => [
            'previous' => null,
            'next'     => null,
            'count'    => count($data),
        ],
    ]);
}
