<?php

namespace App\Commands;

use App\Helpers\Configuration;
use LaravelZero\Framework\Commands\Command;

class BaseCommand extends Command
{
    protected $signature = 'BaseCommand';
    protected $description = 'BaseCommand';

    public function get(string $key, $team = 'default'): string
    {
        return Configuration::get($key, $team);
    }
}
