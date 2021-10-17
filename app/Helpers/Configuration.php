<?php

namespace App\Helpers;

use Exception;

class Configuration
{
    protected array $config;

    public function __construct()
    {
        $this->config = $this->readConfig();
    }

    public function isConfigured(): bool
    {
        if (!file_exists($this->configFilePath())) {
            return false;
        }
        return true;
    }

    public function get(string $key, $profile = 'default'): string
    {
        if (empty($this->config)) {
            return '';
        }

        if (!$this->teamExists($profile)) {
            return '';
        }

        $profilenConfig = $this->config[$profile];

        if (!isset($profilenConfig[$key])) {
            throw new Exception("The key {$key} doesn't exist in the configuration");
        }

        return $profilenConfig[$key];
    }

    public function saveConfig(string $apiKey, string $defaultFormat, string $profile = 'default'): void
    {
        $profileConfig = [
            'api_token' => $apiKey,
            'format'    => $defaultFormat,
        ];
        $this->config[$profile] = $profileConfig;
        file_put_contents($this->configFilePath(), json_encode($this->config));
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
