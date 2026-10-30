<?php

namespace App\Commands\Sites\Domains;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;

abstract class Domains extends BaseCommand
{
    use HasSiteIdParameter;

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
