<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

class EnableCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:enable
                            {site_id? : The site to enable}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Enable a disabled site so it serves traffic again';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to enable', fn ($site) => !empty($site->disabled_at), 'There are no disabled sites.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->enable();

        $this->displaySiteEvent($site, $eventId, 'Site queued for enabling.');

        return self::SUCCESS;
    }
}
