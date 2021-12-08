<?php

namespace App\Commands;

class ConfigureCommand extends BaseCommand
{
    protected $signature = 'configure {--profile=default}';

    protected $description = 'Configure SpinupWP CLI';

    protected bool $requiresToken = false;

    public function handle(): int
    {
        $profile = $this->option('profile');

        if (!is_string($profile)) {
            $profile = 'default';
        }

        if (!empty($this->config->get('api_token', $profile))) {
            $this->alert("A profile named \"{$profile}\" is already configured");

            do {
                $response = strtolower($this->ask('Do you want to overwrite the existing profile? (y/n)', 'y'));
            } while (!in_array($response, ['y', 'n']));

            if ($response === 'n') {
                return self::SUCCESS;
            }
        }

        $apiKey = null;

        while (!$apiKey) {
            $apiKey = $this->ask('SpinupWP API token');
        }

        $defaultFormat = null;

        while (!in_array($defaultFormat, config('app.output_formats'))) {
            $defaultFormat = $this->anticipate('Default output format (json/table)', [
                'json',
                'table',
            ], 'table');
        }

        $this->config->set('api_token', $apiKey, $profile);
        $this->config->set('format', $defaultFormat, $profile);

        $this->info('Profile configured successfully.');

        if ($profile !== 'default') {
            $this->line('To use this profile, add the --profile option to your command:');
            $this->line("`spinupwp servers:list --profile={$profile}`");
        }

        return self::SUCCESS;
    }

    protected function action(): int
    {
        return self::INVALID;
    }
}
