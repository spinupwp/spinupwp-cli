<?php

namespace App\Commands\Sites\PageCache;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;

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
            'cookie_exclusions'    => $this->multilineOption('cookie-exclusion'),
            'ignored_query_params' => $this->multilineOption('ignored-query-param'),
        ]);
    }
}
