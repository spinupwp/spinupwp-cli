<?php

namespace App\Commands\Sites\Domains;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;
use App\Questions\Ask;
use App\Questions\Choice;
use Exception;
use SpinupWp\Resources\Site;

abstract class Domains extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected const REDIRECT_TYPES = [
        301 => 'Permanent (301)',
        302 => 'Temporary (302)',
        307 => 'Temporary, keeping the request method (307)',
        308 => 'Permanent, keeping the request method (308)',
    ];

    protected array $validationLabels = [
        'domain'               => 'Domain',
        'redirect.enabled'     => 'Redirect',
        'redirect.type'        => 'Redirect Type',
        'redirect.destination' => 'Redirect Destination',
    ];

    /**
     * Find the additional domain named by the domain argument (an ID or domain name), or ask which one.
     *
     * @return array<mixed>|null
     */
    protected function selectDomain(Site $site, string $question): ?array
    {
        $domains = collect($site->listDomains());

        if ($domains->isEmpty()) {
            $this->warn('No additional domains found.');
            return null;
        }

        $argument = $this->argument('domain');

        if (is_string($argument) && $argument !== '') {
            $domain = $domains->first(fn ($domain) => (string) ($domain['id'] ?? '') === $argument || strcasecmp($domain['domain'] ?? '', $argument) === 0);

            if (is_null($domain)) {
                throw new Exception("No additional domain \"{$argument}\" found on \"{$site->domain}\".");
            }

            return $domain;
        }

        $selected = $this->askToSelect($question, $domains->keyBy('id')->map(fn ($domain) => $domain['domain'] ?? '')->all());

        return $domains->first(fn ($domain) => ($domain['id'] ?? null) === $selected);
    }

    /**
     * Build the redirect settings, asking for the type and destination when the redirect is enabled.
     *
     * @return array<string, mixed>
     */
    protected function redirectPayload(bool $enabled, string $defaultType, string $defaultDestination): array
    {
        if (!$enabled) {
            return ['enabled' => false];
        }

        $type = Choice::make('Redirect type')
            ->withFlag('redirect-type')
            ->withChoices(self::REDIRECT_TYPES)
            ->withDefault($defaultType)
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        // The status codes are integer keys, so the choice prompt answers with the label rather than
        // the code. Accept either.
        $code = array_search($type, self::REDIRECT_TYPES, true);
        $type = $code === false ? $type : $code;

        if (!is_numeric($type) || !array_key_exists((int) $type, self::REDIRECT_TYPES)) {
            throw new Exception('The --redirect-type option must be 301, 302, 307 or 308.');
        }

        $destination = Ask::make('Redirect to')
            ->withFlag('redirect-destination')
            ->withDefault($defaultDestination)
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        return [
            'enabled'     => true,
            'type'        => (int) $type,
            'destination' => $destination,
        ];
    }

    /**
     * Describe an additional domain's redirect, e.g. "301 to hellfish.media".
     *
     * @param array<mixed> $domain
     */
    protected function describeRedirect(array $domain): string
    {
        if (empty($domain['redirect']['enabled'])) {
            return 'No';
        }

        return trim(($domain['redirect']['type'] ?? '') . ' to ' . ($domain['redirect']['destination'] ?? ''));
    }
}
