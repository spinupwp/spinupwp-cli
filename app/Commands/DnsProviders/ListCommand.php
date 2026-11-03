<?php

namespace App\Commands\DnsProviders;

use App\Commands\DnsProviders\DnsProviders;

class ListCommand extends DnsProviders
{
    protected $signature = 'dns-providers:list
                            {--fields= : The fields to output}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "List your team's DNS providers";

    protected function action(): int
    {
        $dnsProviders = $this->spinupwp->listDnsProviders();

        if ($dnsProviders->isEmpty()) {
            $this->warn('No DNS providers found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $dnsProviders->transform(fn ($dnsProvider) => $this->specifyFields($dnsProvider));
            $this->format($dnsProviders);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $dnsProviders->transform(fn ($dnsProvider) => $this->specifyFields($dnsProvider));
        }

        $this->format($dnsProviders);

        return self::SUCCESS;
    }
}
