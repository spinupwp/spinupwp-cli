<?php

namespace App\Commands\Sites\Domains;

class ListCommand extends Domains
{
    protected $signature = 'sites:domains:list
                            {site_id? : The site to list additional domains for}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'List the additional domains for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to list additional domains for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $domains = collect($site->listDomains());

        if ($domains->isEmpty()) {
            $this->warn('No additional domains found.');
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $domains = $domains->map(fn ($domain) => [
                'ID'         => $domain['id'] ?? '',
                'Domain'     => $domain['domain'] ?? '',
                'Redirect'   => $this->describeRedirect($domain),
                'Created At' => $domain['created_at'] ?? '',
            ]);
        }

        $this->format($domains);

        return self::SUCCESS;
    }
}
