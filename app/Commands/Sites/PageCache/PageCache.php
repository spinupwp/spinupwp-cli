<?php

namespace App\Commands\Sites\PageCache;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;
use Exception;

abstract class PageCache extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected function settingsPayload(): array
    {
        return $this->withoutNulls([
            'duration'             => $this->intOption('duration'),
            'duration_unit'        => $this->stringOption('duration-unit'),
            'path_exclusions'      => $this->multilineOption('path-exclusion'),
            'cookie_exclusions'    => $this->clearableMultilineOption('cookie-exclusion'),
            'ignored_query_params' => $this->clearableMultilineOption('ignored-query-param'),
        ]);
    }

    /**
     * Like multilineOption(), but passing "none" sends an empty value to clear the setting.
     */
    protected function clearableMultilineOption(string $name): ?string
    {
        $values = $this->option($name);

        if (is_array($values) && in_array('none', $values, true)) {
            if (count($values) > 1) {
                throw new Exception("The --{$name} option can't combine \"none\" with other values.");
            }

            return '';
        }

        return $this->multilineOption($name);
    }
}
