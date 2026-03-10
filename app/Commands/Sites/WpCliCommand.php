<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class WpCliCommand extends BaseCommand
{
    protected $signature = 'sites:wp-cli
                            {site_id? : The site to run WP-CLI commands on}
                            {--command=* : The WP-CLI command(s) to run}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Run WP-CLI commands on a site';

    public function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to run WP-CLI commands on');
        }

        $commands = $this->option('command');

        if (empty($commands)) {
            $commands = $this->askForCommands();
        }

        if (empty($commands)) {
            $this->warn('No commands provided.');
            return self::SUCCESS;
        }

        $eventId = $this->spinupwp->sites->wpCli((int) $siteId, $commands);

        $this->successfulStep('WP-CLI commands queued for execution.');

        $this->streamOutput($eventId);

        return self::SUCCESS;
    }

    protected function askForCommands(): array
    {
        $commands = [];

        $this->step('Enter WP-CLI commands (empty line to finish):');

        while (true) {
            $command = $this->ask('Command');

            if (empty($command)) {
                break;
            }

            $commands[] = $command;
        }

        return $commands;
    }

    protected function streamOutput(int $eventId): void
    {
        $displayedLength = 0;

        while (true) {
            sleep(2);

            $event  = $this->spinupwp->events->get($eventId);
            $output = $event->output ?? '';

            if (strlen($output) > $displayedLength) {
                $this->output->write(substr($output, $displayedLength));
                $displayedLength = strlen($output);
            }

            if (in_array($event->status, ['deployed', 'failed'])) {
                break;
            }
        }
    }
}
