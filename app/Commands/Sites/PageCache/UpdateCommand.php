<?php

namespace App\Commands\Sites\PageCache;

class UpdateCommand extends PageCache
{
    protected $signature = 'sites:page-cache:update
                            {site_id? : The site to update}
                            {--duration= : The cache duration}
                            {--duration-unit= : The cache duration unit (s, m, h, d, w, M, or y)}
                            {--path-exclusion=* : A path to exclude from caching (repeatable)}
                            {--cookie-exclusion=* : A cookie that bypasses the cache (repeatable), or "none" to clear}
                            {--ignored-query-param=* : A query parameter to ignore when caching (repeatable), or "none" to clear}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Update the page cache settings for a site';

    protected function action(): int
    {
        $payload = $this->settingsPayload();

        $this->ensureOptionsProvided($payload);

        $site = $this->selectSite('Which site would you like to update the page cache settings for', fn ($site) => $site->page_cache['enabled'], 'There are no sites with page cache enabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->updatePageCache($payload);

        $this->displaySiteEvent($site, $eventId, 'Page cache settings queued for update.');

        return self::SUCCESS;
    }
}
