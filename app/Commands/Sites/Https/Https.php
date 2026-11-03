<?php

namespace App\Commands\Sites\Https;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ReadsDeployKeys;
use App\Commands\Concerns\SelectsDnsProvider;
use App\Questions\Ask;
use App\Questions\Choice;
use Exception;
use SpinupWp\Resources\Site;

abstract class Https extends BaseCommand
{
    use HasSiteIdParameter;
    use ReadsDeployKeys;
    use SelectsDnsProvider;

    protected const CERTIFICATE_TYPES = [
        'letsencrypt' => "Let's Encrypt",
        'dns'         => "Let's Encrypt, validated through a DNS provider",
        'custom'      => 'Custom certificate',
    ];

    protected array $validationLabels = [
        'type'            => 'Certificate Type',
        'dns_provider_id' => 'DNS Provider',
        'certificate'     => 'Certificate',
        'private_key'     => 'Private Key',
    ];

    /**
     * Ask for the certificate type, then for a DNS validated certificate, the DNS provider,
     * or for a custom certificate, read the certificate and private key files.
     *
     * @return array<string, string|int>
     */
    protected function certificatePayload(Site $site): array
    {
        $dnsProviderId = $site->dns_provider_id ?? null;

        $type = Choice::make('Which certificate would you like to use?')
            ->withFlag('type')
            ->withChoices(self::CERTIFICATE_TYPES)
            ->withDefault($dnsProviderId ? 'dns' : 'letsencrypt')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        if (!in_array($type, ['letsencrypt', 'webroot', 'dns', 'custom'], true)) {
            throw new Exception('The --type option must be letsencrypt, dns or custom.');
        }

        if ($type === 'dns') {
            return [
                'type'            => 'dns',
                'dns_provider_id' => $this->selectDnsProvider('Which DNS provider should validate the certificate?', true, $dnsProviderId)
                    ?? throw new Exception('A DNS validated certificate needs the --dns-provider-id option.'),
            ];
        }

        // The API calls a Let's Encrypt certificate validated over HTTP "webroot", after how the domain is verified.
        if ($type !== 'custom') {
            return ['type' => 'webroot'];
        }

        $certificate = Ask::make('Path to the certificate file')
            ->withFlag('certificate')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $privateKey = Ask::make('Path to the private key file')
            ->withFlag('private-key')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        if (!is_string($certificate) || $certificate === '' || !is_string($privateKey) || $privateKey === '') {
            throw new Exception('A custom certificate needs both the --certificate and --private-key options.');
        }

        return [
            'type'        => 'custom',
            'certificate' => $this->readKeyFile($certificate),
            'private_key' => $this->readKeyFile($privateKey),
        ];
    }
}
