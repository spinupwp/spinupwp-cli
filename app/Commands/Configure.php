<?php

namespace App\Commands;

class Configure extends BaseCommand
{
    protected $signature = 'configure {--profile=}';

    protected $description = 'Configure the SpinupWP\'s API token';

    public function handle()
    {
        $team = $this->option('profile') ?? 'default';

        if (!empty($this->get('api_token', $team))) {
            $this->alert("A profile named {$team} is already configured");
            $response = $this->ask('Do you want to reconfigure and overwrite existing configuration? (y/n)', 'y');
            while (!in_array($response, ['y', 'n'])) {
                $this->error("Please type 'y' or 'n'");
                $response = $this->ask('Do you want to reconfigure and overwrite existing credentials? (y/n)', 'y');
            }
            if ($response === 'n') {
                return 0;
            }
        }

        $apiKey = null;

        while (!$apiKey) {
            $apiKey = $this->ask("Enter your API token for the {$team} team. You can get from your SpinupWP account page https://spinupwp.app");
        }

        $defaultFormat = null;

        while (!in_array($defaultFormat, config('app.output_formats'))) {
            $defaultFormat = $this->ask('Which format would you prefer for data output? (json/table)', null);
        }

        $this->config->saveConfig($apiKey, $defaultFormat, $team);
        $this->info('SpinupWP CLI configured successfully');
        return 0;
    }
}
