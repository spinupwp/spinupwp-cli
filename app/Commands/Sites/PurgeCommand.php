<?php

namespace App\Commands\Sites;

class PurgeCommand extends \App\Commands\BaseCommand
{
    protected $signature = 'sites:purge {site_id? : The site ID}
                                        {--cache= : The cache to purge}
                                        {--all : Purge cache on all sites}
                                        {--profile=}';

    protected $description = 'Purge the page cache for a site';

    protected function action(): int
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site do you want to purge the page cache for?');
        }

        $cacheToPurge = $this->option('cache');

        if (empty($cacheToPurge)) {
            $cacheToPurge = (int) $this->askToSelect('Which cache do you want to purge?', [
                '1' => 'Page cache',
                '2' => 'Object cache',
            ]);

            $cacheToPurge = $cacheToPurge === 1 ? 'page' : 'object';
        }

        $site = $this->spinupwp->sites->get($siteId);

        $this->purgeCache([$site], $cacheToPurge);

        return self::SUCCESS;
    }

    protected function purgeCacheOnAllSites(): void
    {
    }

    protected function purgeCache($sites, string $cacheToPurge): void
    {
        if (empty($sites)) {
            return;
        }

        foreach ($sites as $site) {
            $response = $cacheToPurge === 'page' ? $site->purgePageCache() : $site->purgeObjectCache();
            $cache    = $cacheToPurge === 'page' ? 'page' : 'object';
            $this->info("Purging {$cache} cache for site {$site->domain}. Event ID: {$response}");
        }
    }
}
