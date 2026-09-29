<?php

namespace App\Commands\Sites\PageCache;

class EnableCommand extends PageCache
{
    protected $signature = 'sites:page-cache:enable
                            {site_id? : The site to enable the page cache for}
                            {--duration= : The cache duration}
                            {--duration-unit= : The cache duration unit (s, m, h, d, w, M, or y)}
                            {--path-exclusion=* : A path to exclude from caching (repeatable)}
                            {--cookie-exclusion=* : A cookie that bypasses the cache (repeatable)}
                            {--ignored-query-param=* : A query parameter to ignore when caching (repeatable)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Enable the page cache for a site';

    protected function action(): int
    {
        $payload = $this->settingsPayload();

        $site = $this->selectSite('Which site would you like to enable the page cache for', fn ($site) => !$site->page_cache['enabled'], 'There are no sites with page cache disabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->enablePageCache($payload);

        $this->displaySiteEvent($site, $eventId, 'Page cache queued for enabling.');

        return self::SUCCESS;
    }
}
