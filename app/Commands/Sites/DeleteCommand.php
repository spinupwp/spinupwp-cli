<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class DeleteCommand extends BaseCommand
{
    protected $signature = "sites:delete
                            {site_id? : The site to delete}
                            {--d|delete-database : Delete the site's associated database}
                            {--b|delete-backups : Delete the site's associated backups}
                            {--f|force : Delete the site without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}";

    protected $description = 'Delete a site';

    public function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to delete');
        }

        $site  = $this->spinupwp->getSite((int) $siteId);
        $force = $this->option('force');

        if (!$force) {
            $this->alert("You're about to delete \"{$site->domain}\"");
            $confirmed = $this->confirm('Do you wish to continue?', false);
        }

        if ($force || $confirmed) {
            $eventId = $site->delete(
                (bool) $this->option('delete-database'),
                (bool) $this->option('delete-backups'),
            );

            $this->successfulStep('Site queued for deletion.');

            $this->stepTable([
                'Event ID',
                'Domain',
            ], [[
                $eventId,
                $site->domain,
            ]]);
        }

        return self::SUCCESS;
    }
}
