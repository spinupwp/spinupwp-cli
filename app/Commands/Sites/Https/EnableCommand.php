<?php

namespace App\Commands\Sites\Https;

class EnableCommand extends Https
{
    protected $signature = 'sites:https:enable
                            {site_id? : The site to enable HTTPS for}
                            {--type= : The certificate to use (letsencrypt or custom)}
                            {--certificate= : For a custom certificate, path to the certificate file}
                            {--private-key= : For a custom certificate, path to the private key file}
                            {--f|force : Run without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Enable HTTPS for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to enable HTTPS for', fn ($site) => empty($site->https['enabled']), 'There are no sites with HTTPS disabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->enableHttps($this->certificatePayload());

        $this->displaySiteEvent($site, $eventId, 'HTTPS queued for enabling.');

        return self::SUCCESS;
    }
}
