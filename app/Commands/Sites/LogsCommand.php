<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\InteractsWithRemote;
use App\Questions\Choice;
use Exception;

class LogsCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use InteractsWithRemote;

    public const LOGS = ['debug.log', 'error.log', 'access.log'];

    protected $signature = 'sites:logs
                            {site_id? : The site to view logs for}
                            {--log= : The log to view (debug.log, error.log, or access.log)}
                            {--lines=100 : The number of lines to display}
                            {--follow : Keep the log open and display new lines as they are written}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "View a site's logs";

    protected function action(): int
    {
        $log = $this->option('log');

        if (!is_null($log) && !in_array($log, self::LOGS, true)) {
            throw new Exception('The --log option must be one of: ' . implode(', ', self::LOGS) . '.');
        }

        $lines = $this->option('lines');

        if (!is_string($lines) || !ctype_digit($lines) || (int) $lines < 1) {
            throw new Exception('The --lines option must be a positive whole number.');
        }

        $site = $this->selectSite('Which site would you like to view logs for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $log = Choice::make('Which log would you like to view')
            ->withFlag('log')
            ->withChoices(self::LOGS)
            ->withDefault(self::LOGS[0])
            ->resolveAnswer($this);

        $server = $this->spinupwp->getServer($site->server_id);

        $this->line("Establishing a secure connection to [<comment>{$server->name}</comment>] as [<comment>{$site->site_user}</comment>]...");

        $follow = $this->option('follow') ? ' -f' : '';

        $exitCode = $this->ssh(
            $site->site_user,
            $server->ip_address,
            $server->ssh_port,
            // Without the trap, Ctrl+C kills the remote shell and ssh exits 255, which looks like a failed connection.
            "trap \"exit 0\" INT; tail -n {$lines}{$follow} ./logs/{$log}",
        );

        if ($exitCode === 255) {
            $this->error("Unable to connect to \"{$server->name}\". Have you added your SSH key to the \"{$site->site_user}\" user?");
        }

        return $exitCode;
    }
}
