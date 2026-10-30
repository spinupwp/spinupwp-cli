<?php

namespace App\Commands\Sites\BasicAuth;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;

class UpdateCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected $signature = 'sites:basic-auth:update
                            {site_id? : The site to update}
                            {--username= : The basic auth username}
                            {--password= : The basic auth password}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Update the basic auth credentials for a site';

    protected function action(): int
    {
        $payload = $this->withoutNulls([
            'username' => $this->stringOption('username'),
            'password' => $this->stringOption('password'),
        ]);

        $this->ensureOptionsProvided($payload);

        $site = $this->selectSite('Which site would you like to update the basic auth credentials for', fn ($site) => $site->basic_auth['enabled'], 'There are no sites with basic auth enabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->updateBasicAuth($payload);

        $this->displaySiteEvent($site, $eventId, 'Basic auth credentials queued for update.', 'Basic auth credentials are already up to date.');

        return self::SUCCESS;
    }
}
