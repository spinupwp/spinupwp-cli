<?php

namespace App\Commands\Providers;

use App\Commands\Concerns\ParsesOptions;

class SizesCommand extends Providers
{
    use ParsesOptions;

    protected $signature = 'providers:sizes
                            {provider? : The server provider (digitalocean, hetzner, linode or vultr)}
                            {--region= : Only list the sizes offered in this region}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'List the server sizes a server provider offers';

    protected function action(): int
    {
        $provider = $this->selectProvider($this->argument('provider'));
        $sizes    = collect($this->sizes($this->spinupwp->providerMetadata($provider), $this->stringOption('region')));

        if ($sizes->isEmpty()) {
            $this->warn('No sizes found.');
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $sizes = $sizes->map(fn ($size) => [
                'Slug'      => $size['slug'] ?? '',
                'vCPUs'     => $size['vcpus'] ?? '',
                'Memory'    => $this->formatMemory($size['memory'] ?? 0),
                'Disk'      => ($size['disk'] ?? '') . ' GB',
                'Transfer'  => round((float) ($size['transfer'] ?? 0), 2) . ' TB',
                'Price'     => '$' . number_format((float) ($size['priceMonthly'] ?? 0), 2) . '/month',
                'Available' => empty($size['available']) ? 'No' : 'Yes',
            ]);
        }

        $this->format($sizes);

        return self::SUCCESS;
    }
}
