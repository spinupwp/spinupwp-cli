<?php

namespace App\Helpers;

class Configuration
{
    public static function isConfigured(): bool
    {
        $configPath = static::getConfigPath() . 'config';
        if (!file_exists($configPath)) {
            return false;
        }
        return true;
    }

    public static function getCredentials($team = 'default'): string
    {
        if (!static::isConfigured()) {
            return "";
        }

        $credentialsConfiguration = file_get_contents(static::getConfigPath() . 'config');

        preg_match_all("/\[{$team}\](.*)\[\/{$team}\]/s", $credentialsConfiguration, $matches);

        if (!isset($matches[1][0])) {
            return "";
        }

        preg_match("/api_token\s=\s(?<token>[a-zA-Z0-9\.\-_]+)/s", trim($matches[1][0]), $data);

        if (!isset($data['token']) || empty($data['token'])) {
            return "";
        }

        return $data['token'];
    }

    public static function saveCredentials(string $apiKey, string $defaultFormat, string $team = 'default'): void
    {
        $configuration = "";

        $newTeam = "[{$team}]\napi_token = {$apiKey}\nformat = {$defaultFormat}\n[/{$team}]";

        $configFile = static::getConfigPath() . 'config';

        if (static::isConfigured()) {
            $configuration = file_get_contents(static::getConfigPath() . 'config');
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

    public static function getConfigPath(): string
    {
        $userHome = config('app.config_path') . '/.spinupwp/';
        if (!file_exists($userHome)) {
            mkdir($userHome);
        }
        return $userHome;
    }
}
