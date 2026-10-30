<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DeleteCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = "sites:delete
                            {site_id? : The site to delete}
                            {--d|delete-database : Delete the site's associated database}
                            {--b|delete-backups : Delete the site's associated backups}
                            {--f|force : Delete the site without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}";

    protected $description = 'Delete a site';

    public function action(): int
    {
        $site = $this->selectSite('Which site would you like to delete');

        if (is_null($site)) {
            return self::SUCCESS;
        }

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

            $this->displaySiteEvent($site, $eventId, 'Site queued for deletion.');
        }

        return self::SUCCESS;
    }
}
