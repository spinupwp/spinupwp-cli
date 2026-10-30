<?php

namespace App\Commands\Sites\Https;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ReadsDeployKeys;
use App\Questions\Ask;
use App\Questions\Choice;
use Exception;

abstract class Https extends BaseCommand
{
    use HasSiteIdParameter;
    use ReadsDeployKeys;

    protected const CERTIFICATE_TYPES = [
        'letsencrypt' => "Let's Encrypt",
        'custom'      => 'Custom certificate',
    ];

    protected array $validationLabels = [
        'type'        => 'Certificate Type',
        'certificate' => 'Certificate',
        'private_key' => 'Private Key',
    ];

    /**
     * Ask for the certificate type, and for a custom certificate, read the certificate and private key files.
     *
     * @return array<string, string>
     */
    protected function certificatePayload(): array
    {
        $type = Choice::make('Which certificate would you like to use?')
            ->withFlag('type')
            ->withChoices(self::CERTIFICATE_TYPES)
            ->withDefault('letsencrypt')
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        if (!in_array($type, ['letsencrypt', 'webroot', 'custom'], true)) {
            throw new Exception('The --type option must be letsencrypt or custom.');
        }

        // The API calls a Let's Encrypt certificate "webroot", after how the domain is verified.
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
