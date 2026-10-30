<?php

namespace App\Commands\Sites\Https;

class UpdateCommand extends Https
{
    protected $signature = 'sites:https:update
                            {site_id? : The site to update the HTTPS certificate for}
                            {--type= : The certificate to use (letsencrypt or custom)}
                            {--certificate= : For a custom certificate, path to the certificate file}
                            {--private-key= : For a custom certificate, path to the private key file}
                            {--f|force : Run without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Switch the HTTPS certificate for a site, or replace its custom certificate';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to update the HTTPS certificate for', fn ($site) => !empty($site->https['enabled']), 'There are no sites with HTTPS enabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->updateHttps($this->certificatePayload());

        $this->displaySiteEvent($site, $eventId, 'HTTPS certificate queued for update.');

        return self::SUCCESS;
    }
}
