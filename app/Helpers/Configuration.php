<?php

namespace App\Helpers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class Configuration
{
    public static string $profile;

    public static $customHttpClient = null;

    public static function setCustomHttpClient(string $profile, Client $client = null): void
    {
        $config                   = app('App\Helpers\Configuration');
        static::$customHttpClient = $client ?? new Client([
            'base_uri'    => $config->get('api_url', $profile),
            'http_errors' => false,
            'headers'     => [
                'Authorization' => "Bearer {$config->get('api_token', $profile)}",
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public static function getCustomHttpClient(string $profile): Client
    {
        if (is_null(static::$customHttpClient)) {
            static::setCustomHttpClient($profile);
        }
        return static::$customHttpClient;
    }

    /**
     * @var array
     */
    protected $config;

    public function __construct()
    {
        $this->config = $this->readConfig();
    }

    public function isConfigured(): bool
    {
        return file_exists($this->configFilePath());
    }

    public function get(string $key, string $profile = 'default'): string
    {
        if (empty($this->config)) {
            return '';
        }

        if (!$this->teamExists($profile)) {
            return '';
        }

        $profileConfig = $this->config[$profile];

        if (!isset($profileConfig[$key])) {
            return '';
        }

        return $profileConfig[$key];
    }

    public function set(string $key, string $value, string $profile = 'default'): void
    {
        $config = $this->config;

        Arr::set($config, "{$profile}.{$key}", $value);

        file_put_contents($this->configFilePath(), json_encode($config, JSON_PRETTY_PRINT));

        $this->config = $config;
    }

    public function teamExists(string $profile): bool
    {
        return isset($this->config[$profile]);
    }

    protected function readConfig(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $configFile = file_get_contents($this->configFilePath());

        if (!$configFile) {
            return [];
        }

        return json_decode($configFile, true);
    }

    public function configFilePath(): string
    {
        return $this->getConfigPath() . 'config.json';
    }

    protected function getConfigPath(): string
    {
        $userHome = config('app.config_path') . '/.spinupwp/';

        if (!file_exists($userHome)) {
            mkdir($userHome);
        }

        return $userHome;
    }
}
