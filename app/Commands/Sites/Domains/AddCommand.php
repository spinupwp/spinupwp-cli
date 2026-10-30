<?php

namespace App\Commands\Sites\Domains;

use App\Questions\Ask;
use App\Questions\Confirm;

class AddCommand extends Domains
{
    protected $signature = 'sites:domains:add
                            {site_id? : The site to add a domain to}
                            {--domain= : The additional domain}
                            {--redirect : Redirect the domain instead of serving the site on it}
                            {--redirect-type= : The redirect status code (301, 302, 307 or 308)}
                            {--redirect-destination= : The domain or URL to redirect to (defaults to the primary domain)}
                            {--f|force : Run without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Add an additional domain to a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to add a domain to');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $domain = Ask::make('Domain')
            ->withFlag('domain')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $redirect = (bool) Confirm::make("Redirect this domain to \"{$site->domain}\" or another URL?")
            ->withFlag('redirect')
            ->withDefault(false)
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $response = $site->addDomain([
            'domain'   => $domain,
            'redirect' => $this->redirectPayload($redirect, '301', $site->domain),
        ]);

        $this->displaySiteEvent($site, $response['event_id'] ?? null, "Domain \"{$domain}\" queued for adding.");

        return self::SUCCESS;
    }
}
