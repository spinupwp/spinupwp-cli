<?php

namespace App\Commands\Concerns;

use SpinupWp\Resources\Site;

trait HasSiteIdParameter
{
    protected function selectSite(string $question, ?callable $filter = null, string $noSitesMessage = 'No sites found.'): ?Site
    {
        $siteId = $this->argument('site_id');

        if (empty($siteId)) {
            $siteId = $this->askToSelectSite($question, $filter);
        }

        if (empty($siteId)) {
            $this->warn($noSitesMessage);
            return null;
        }

        return $this->spinupwp->getSite((int) $siteId);
    }

    /**
     * @param int|string|null $eventId
     */
    protected function displaySiteEvent(Site $site, $eventId, string $queuedMessage, string $savedMessage = ''): void
    {
        if (is_null($eventId)) {
            $this->successfulStep($savedMessage ?: $queuedMessage);
            return;
        }

        $this->successfulStep($queuedMessage);

        $this->stepTable([
            'Event ID',
            'Site',
        ], [[
            $eventId,
            $site->domain,
        ]]);
    }
}
