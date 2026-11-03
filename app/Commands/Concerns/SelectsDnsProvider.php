<?php

namespace App\Commands\Concerns;

use Exception;

trait SelectsDnsProvider
{
    use ParsesOptions;

    /**
     * The --dns-provider-id option, or else the DNS provider picked from the team's list.
     * Returns null when no DNS provider is chosen.
     */
    protected function selectDnsProvider(string $question, bool $required, ?int $default = null): ?int
    {
        $dnsProviderId = $this->intOption('dns-provider-id');

        if (!is_null($dnsProviderId) || $this->nonInteractive()) {
            return $dnsProviderId ?? $default;
        }

        $choices = $this->spinupwp->listDnsProviders()
            ->keyBy('id')
            ->map(fn ($dnsProvider) => "{$dnsProvider->name} ({$dnsProvider->service})")
            ->toArray();

        if (empty($choices)) {
            if ($required) {
                throw new Exception('No DNS providers found. Add one in your SpinupWP team settings first.');
            }

            return null;
        }

        if (!$required) {
            $choices = [0 => 'None'] + $choices;
        }

        $default = !is_null($default) && isset($choices[$default]) ? $default : ($required ? null : 0);

        return $this->askToSelect($question, $choices, $default) ?: null;
    }
}
