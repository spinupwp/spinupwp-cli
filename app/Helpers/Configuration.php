<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class Configuration
{
    protected string $path;

    protected array $config;

    public function __construct(string $path)
    {
        $this->path   = $path;
        $this->config = $this->readConfig();
    }

    public function isConfigured(): bool
    {
        return file_exists($this->configFilePath());
    }

    /**
     * @param string $key
     * @param string $profile
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, string $profile = 'default', $default = null)
    {
        $this->config = $this->readConfig();

        if (empty($this->config)) {
            return $default;
        }

        if (!$this->teamExists($profile)) {
            return $default;
        }

        return Arr::get($this->config[$profile], $key, $default);
    }

    public function set(string $key, string $value, string $profile = 'default'): void
    {
        $config = $this->config;

        Arr::set($config, "{$profile}.{$key}", $value);

        file_put_contents($this->configFilePath(), json_encode($config, JSON_PRETTY_PRINT));

        $this->config = $config;
    }

    public function getCommandConfiguration(string $command, string $profile = 'default'): array
    {
        $command = trim($command);
        return $this->get("command_options.{$command}", $profile, []);
    }

    public function setCommandConfiguration(string $command, string $key, string $value, string $profile = 'default'): void
    {
        $command = trim($command);

        $this->set("command_options.{$command}.{$key}", $value, $profile);
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
        if (!File::isDirectory($this->path)) {
            File::makeDirectory($this->path);
        }

        return $this->path . 'config.json';
    }

    public function sshControlPath(): string
    {
        $sshPath = $this->path . 'ssh/';

        if (!File::isDirectory($sshPath)) {
            File::makeDirectory($sshPath);
        }

        return $sshPath . '%h-%p-%r';
    }
}
