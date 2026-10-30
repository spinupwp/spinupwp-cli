<?php

namespace App\Commands\Sites\PageCache;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class DisableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:page-cache:disable
                            {site_id? : The site to disable the page cache for}
                            {--f|force : Disable the page cache without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Disable the page cache for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to disable the page cache for', fn ($site) => $site->page_cache['enabled'], 'There are no sites with page cache enabled.');

        if (is_null($site) || !$this->forceOrConfirm("Are you sure you want to disable the page cache for \"{$site->domain}\"?")) {
            return self::SUCCESS;
        }

        $eventId = $site->disablePageCache();

        $this->displaySiteEvent($site, $eventId, 'Page cache queued for disabling.');

        return self::SUCCESS;
    }
}
