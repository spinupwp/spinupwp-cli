<?php

namespace App\Commands;

use App\Helpers\Configuration;
use LaravelZero\Framework\Commands\Command;

abstract class BaseCommand extends Command
{
    protected Configuration $config;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();
        $this->config = $configuration;
    }
}
