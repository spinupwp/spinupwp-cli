<?php

namespace App\Commands\Sites;

use Illuminate\Support\Collection;

class PurgeCommand extends \App\Commands\BaseCommand
{
    protected $signature = 'sites:purge {site_id? : The site to purge}
                                        {--cache= : The cache to purge}
                                        {--all : Purge cache on all sites}
                                        {--profile=}';

    protected $description = 'Purge the page or object cache for a site';

    protected function action(): int
    {
        $cacheToPurge = strval($this->option('cache'));

        if (empty($cacheToPurge)) {
            $cacheToPurge = (int) $this->askToSelect('Which cache do you want to purge', [
                '1' => 'Page cache',
                '2' => 'Object cache',
            ], '1');

            $cacheToPurge = $cacheToPurge === 1 ? 'page' : 'object';
        }

        if ($this->option('all')) {
            $this->purgeCacheOnAllSites($cacheToPurge);
            return self::SUCCESS;
        }

        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite('Which site do you want to purge the page cache for', function ($site) use ($cacheToPurge) {
                if ($cacheToPurge === 'page') {
                    return $site->page_cache['enabled'];
                }
                return $site->is_wordpress;
            });
        }

        if ($siteId === 0) {
            $this->warn("There are no sites with {$cacheToPurge} cache enabled");
            return self::SUCCESS;
        }

        $site = $this->spinupwp->sites->get(intval($siteId));

        $this->purgeCache(collect([$site]), $cacheToPurge);

        return self::SUCCESS;
    }

    protected function purgeCacheOnAllSites(string $cacheToPurge): void
    {
        $sites = collect($this->spinupwp->sites->list());
        if ($cacheToPurge === 'page') {
            $sites = $sites->filter(fn ($site) => $site->page_cache['enabled']);
        }
        if ($cacheToPurge === 'object') {
            $sites = $sites->filter(fn ($site) => $site->is_wordpress);
        }

        if ($sites->isEmpty()) {
            $this->warn("There are no sites with {$cacheToPurge} cache enabled");
            return;
        }

        $shouldWait = $sites->count() > 55;

        $this->purgeCache($sites, $cacheToPurge, $shouldWait);
    }

    protected function purgeCache(Collection $sites, string $cacheToPurge, bool $shouldWait = false): void
    {
        if ($sites->isEmpty()) {
            return;
        }

        $endpoint = $cacheToPurge === 'page' ? 'purgePageCache' : 'purgeObjectCache';
        $verb     = "purging {$cacheToPurge} cache";
        $this->queueResources($sites, $endpoint, $verb, 'domain', $shouldWait);
    }
}
