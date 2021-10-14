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
        $configPath = $this->getConfigPath() . 'config';
        if (!file_exists($configPath)) {
            return false;
        }
        return true;
    }

    public function get(string $key, $team = 'default'): string
    {
        if (empty($this->config)) {
            return '';
        }

        if (!$this->teamExists($team)) {
            throw new Exception("The profifle {$team} doesn't exist");
        }

        $teamnConfig = $this->config[$team];

        if (!isset($teamnConfig[$key])) {
            throw new Exception("The key {$key} doesn't exist in the configuration");
        }

        return $teamnConfig[$key];
    }

    public function saveConfig(string $apiKey, string $defaultFormat, string $team = 'default'): void
    {
        $teamConfig = [
            'api_token' => $apiKey,
            'format'    => $defaultFormat,
        ];
        $this->config[$team] = $teamConfig;
        file_put_contents($this->configFilePath(), json_encode($this->config));
    }

    public function teamExists(string $team): bool
    {
        return isset($this->config[$team]);
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
        return $this->getConfigPath() . 'config';
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
