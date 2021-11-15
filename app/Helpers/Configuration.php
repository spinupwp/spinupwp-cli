<?php

namespace App\Helpers;

use Illuminate\Support\Arr;

class Configuration
{
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
        $this->config = $this->readConfig();
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
