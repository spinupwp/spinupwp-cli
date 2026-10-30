<?php

namespace App\Commands\Providers;

class RegionsCommand extends Providers
{
    protected $signature = 'providers:regions
                            {provider? : The server provider (digitalocean, hetzner, linode or vultr)}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'List the regions a server provider offers';

    protected function action(): int
    {
        $provider = $this->selectProvider($this->argument('provider'));
        $regions  = collect($this->regions($this->spinupwp->providerMetadata($provider)));

        if ($regions->isEmpty()) {
            $this->warn('No regions found.');
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $this->format($regions->map(fn ($region) => [
                'Slug'      => $region['slug'],
                'Name'      => $region['name'],
                'Continent' => $region['continent'],
                'Available' => $region['available'] ? 'Yes' : 'No',
            ]));

            return self::SUCCESS;
        }

        $this->format($regions->map(fn ($region) => collect($region)->except('sizes')->all()));

        return self::SUCCESS;
    }
}
