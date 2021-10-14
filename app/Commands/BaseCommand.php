<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class BaseCommand extends Command
{
    protected $signature = 'BaseCommand';

    protected $description = 'BaseCommand';

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
        if (!$this->isConfigured()) {
            return "";
        }

        $configFile = file_get_contents($this->getConfigPath() . 'config');

        preg_match_all("/\[{$team}\](.*)\[\/{$team}\]/s", $configFile, $matches);

        if (!isset($matches[1][0])) {
            return "";
        }

        preg_match("/{$key}\s=\s(?<token>[a-zA-Z0-9\.\-_]+)/s", trim($matches[1][0]), $data);

        if (!isset($data['token']) || empty($data['token'])) {
            return "";
        }

        return $data['token'];
    }

    public function saveConfig(string $apiKey, string $defaultFormat, string $team = 'default'): void
    {
        $configuration = "";

        $newTeam = "[{$team}]\napi_token = {$apiKey}\nformat = {$defaultFormat}\n[/{$team}]";

        $configFile = $this->getConfigPath() . 'config';

        if ($this->isConfigured()) {
            $configuration = file_get_contents($this->getConfigPath() . 'config');
            preg_match_all("/\[{$team}\](.*)\[\/{$team}\]/s", $configuration, $matches);
            if (isset($matches[1][0])) {
                file_put_contents($configFile, preg_replace("/\[{$team}\](.*)\[\/{$team}]/s", $newTeam, $configuration));
                return;
            }
            file_put_contents($configFile, "{$configuration}\n\n{$newTeam}");
            return;
        }

        file_put_contents($configFile, $newTeam);
    }

    public function getConfigPath(): string
    {
        $userHome = config('app.config_path') . '/.spinupwp/';
        if (!file_exists($userHome)) {
            mkdir($userHome);
        }
        return $userHome;
    }
}
