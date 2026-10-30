<?php

namespace App\Commands\Sites\Domains;

class DeleteCommand extends Domains
{
    protected $signature = 'sites:domains:delete
                            {site_id? : The site the domain belongs to}
                            {domain? : The ID or name of the additional domain to delete}
                            {--f|force : Delete the domain without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Delete an additional domain from a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to delete a domain from');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $domain = $this->selectDomain($site, 'Which domain would you like to delete');

        if (is_null($domain) || !$this->forceOrConfirm("Are you sure you want to delete \"{$domain['domain']}\" from \"{$site->domain}\"?", false)) {
            return self::SUCCESS;
        }

        $eventId = $site->deleteDomain((int) $domain['id']);

        $this->displaySiteEvent($site, $eventId, "Domain \"{$domain['domain']}\" queued for deletion.");

        return self::SUCCESS;
    }
}
