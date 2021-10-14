<?php

namespace App\Commands;

use App\Helpers\Configuration;
use LaravelZero\Framework\Commands\Command;

class BaseCommand extends Command
{
    protected $signature = 'BaseCommand';
    protected $description = 'BaseCommand';

    protected Configuration $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = new Configuration();
    }

    public function get(string $key, $team = 'default'): string
    {
        return $this->config->get($key, $team);
    }
}
