<?php

namespace App\Commands\Providers;

use App\Commands\BaseCommand;
use App\Helpers\OptionsHelper;
use Exception;

abstract class Providers extends BaseCommand
{
    protected function selectProvider(): string
    {
        $provider = $this->argument('provider');

        if (!is_string($provider) || $provider === '') {
            $choice   = $this->choice('Which server provider', OptionsHelper::SERVER_PROVIDERS);
            $provider = is_string($choice) ? $choice : '';
        }

        $provider = strtolower($provider);

        if (!array_key_exists($provider, OptionsHelper::SERVER_PROVIDERS)) {
            throw new Exception('The provider must be one of: ' . implode(', ', array_keys(OptionsHelper::SERVER_PROVIDERS)) . '.');
        }

        return $provider;
    }

    /**
     * The provider's regions as a flat list. The API groups regions by continent.
     *
     * @param array<mixed> $metadata
     * @return array<int, array<string, mixed>>
     */
    protected function regions(array $metadata): array
    {
        $regions = [];

        foreach ($metadata['regions'] ?? [] as $continent => $group) {
            // Support an ungrouped list of regions too.
            if (is_array($group) && isset($group['slug'])) {
                $group     = [$group];
                $continent = '';
            }

            foreach (is_array($group) ? $group : [] as $region) {
                $regions[] = [
                    'slug'      => $region['slug'] ?? '',
                    'name'      => $region['name'] ?? '',
                    'continent' => is_string($continent) ? $continent : '',
                    'available' => (bool) ($region['available'] ?? false),
                    'sizes'     => $region['sizes'] ?? [],
                ];
            }
        }

        return $regions;
    }
}
