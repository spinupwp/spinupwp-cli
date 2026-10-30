<?php

namespace App\Commands\Providers;

use App\Commands\BaseCommand;
use App\Helpers\OptionsHelper;
use Exception;

abstract class Providers extends BaseCommand
{
    protected function selectProvider(mixed $provider, string $question = 'Which server provider'): string
    {
        if (!is_string($provider) || $provider === '') {
            $choice   = $this->choice($question, OptionsHelper::SERVER_PROVIDERS);
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

    /**
     * The provider's sizes, limited to those offered in a region when one is given.
     *
     * @param array<mixed> $metadata
     * @return array<int, array<string, mixed>>
     */
    protected function sizes(array $metadata, ?string $region = null): array
    {
        $sizes = array_values(array_filter($metadata['sizes'] ?? [], 'is_array'));

        if (is_null($region)) {
            return $sizes;
        }

        $match = collect($this->regions($metadata))->firstWhere('slug', $region);

        if (is_null($match)) {
            throw new Exception("No region \"{$region}\" found. Run providers:regions to see the available regions.");
        }

        return array_values(array_filter($sizes, fn ($size) => in_array($size['slug'] ?? null, $match['sizes'], true)));
    }

    protected function formatMemory(mixed $megabytes): string
    {
        $megabytes = (int) $megabytes;

        return $megabytes >= 1024 ? round($megabytes / 1024, 1) . ' GB' : "{$megabytes} MB";
    }

    /**
     * Describe a size in one line, e.g. "2 vCPUs, 4 GB memory, 80 GB disk, $24.00/month".
     *
     * @param array<mixed> $size
     */
    protected function describeSize(array $size): string
    {
        return sprintf(
            '%s vCPUs, %s memory, %s GB disk, $%s/month',
            $size['vcpus'] ?? '?',
            $this->formatMemory($size['memory'] ?? 0),
            $size['disk'] ?? '?',
            number_format((float) ($size['priceMonthly'] ?? 0), 2),
        );
    }
}
