<?php

namespace App\Commands;

use App\Helpers\Configuration;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Configure extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'configure {--team=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Configure the SpinupWP\'s API token';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $team = $this->option('team') ?? 'default';
        if (!empty(Configuration::getCredentials($team))) {
            $this->alert("The {$team} team is already configured");
            $response = $this->ask('Do you want to reconfigure and overwrite existing credentials? (y/n)', 'y');
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
            $apiKey = $this->ask("Enter your API token for the {$team} team. You can get from your account page in https://spinupwp.app");
        }
        $defaultFormat = null;
        while (!in_array($defaultFormat, ['json', 'table'])) {
            $defaultFormat = $this->ask('Which format would you prefer for data output? (json/table)', null);
        }
        Configuration::saveCredentials($apiKey, $defaultFormat, $team);
        $this->info("SpinupWP CLI configured successfuly");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
