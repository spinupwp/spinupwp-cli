<?php

namespace App\Commands\Sites\Redirects;

use App\Commands\Sites\Redirects\Redirects;

class ListCommand extends Redirects
{
    protected $signature = 'sites:redirects:list
                            {site_id? : The site to list path redirects for}
                            {--fields= : The fields to output}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'List the path redirects for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to list path redirects for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $redirects = $this->spinupwp->listPathRedirects($site->id);

        if ($redirects->isEmpty()) {
            $this->warn('No path redirects found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $redirects->transform(fn ($redirect) => $this->specifyFields($redirect));
            $this->format($redirects);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $redirects->transform(fn ($redirect) => $this->specifyFields($redirect));
        }

        $this->format($redirects);

        return self::SUCCESS;
    }
}
