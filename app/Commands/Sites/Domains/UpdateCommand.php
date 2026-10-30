<?php

namespace App\Commands\Sites\Domains;

class UpdateCommand extends Domains
{
    protected $signature = 'sites:domains:update
                            {site_id? : The site the domain belongs to}
                            {domain? : The ID or name of the additional domain to update}
                            {--redirect= : Redirect the domain (true or false)}
                            {--redirect-type= : The redirect status code (301, 302, 307 or 308)}
                            {--redirect-destination= : The domain or URL to redirect to}
                            {--f|force : Run without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Update an additional domain's redirect";

    protected function action(): int
    {
        $redirect = $this->boolOption('redirect');

        $site = $this->selectSite('Which site would you like to update a domain for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $domain = $this->selectDomain($site, 'Which domain would you like to update');

        if (is_null($domain)) {
            return self::SUCCESS;
        }

        $current = $domain['redirect'] ?? [];

        if (is_null($redirect)) {
            $redirect = $this->nonInteractive()
                ? !empty($current['enabled'])
                : $this->confirm("Redirect \"{$domain['domain']}\" to \"{$site->domain}\" or another URL?", !empty($current['enabled']));
        }

        $response = $site->updateDomain((int) $domain['id'], [
            'redirect' => $this->redirectPayload(
                $redirect,
                (string) ($current['type'] ?? '301'),
                (string) ($current['destination'] ?? $site->domain),
            ),
        ]);

        $this->displaySiteEvent($site, $response['event_id'] ?? null, "Domain \"{$domain['domain']}\" queued for update.");

        return self::SUCCESS;
    }
}
