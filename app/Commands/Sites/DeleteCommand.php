<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;

class DeleteCommand extends BaseCommand
{
    protected $signature = 'sites:delete
                            {site_id? : The site to delete}
                            {--f|force : Delete the site without prompting for confirmation}
                            {--profile=}';

    protected $description = 'Delete a site';

    public function action(): int
    {
        $siteId = $this->argument('site_id');
        $force  = $this->option('force');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site would you like to delete');
        }

        $site = $this->spinupwp->sites->get((int) $siteId);

        if (!$force) {
            $this->alert("You're about to delete \"{$site->domain}\"");
            $confirmed = $this->confirm('Do you wish to continue?');
        }

        if ($force || $confirmed) {
            $eventId = $this->spinupwp->sites->delete((int)$siteId);

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
