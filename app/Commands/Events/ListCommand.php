<?php

namespace App\Commands\Events;

use App\Commands\Concerns\ParsesOptions;
use Exception;

class ListCommand extends Events
{
    use ParsesOptions;

    protected $signature = 'events:list
                            {--limit=20 : The number of most recent events to list}
                            {--fields= : The fields to output}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "List your team's most recent events";

    protected function action(): int
    {
        $limit = $this->intOption('limit') ?? 20;

        if ($limit < 1) {
            throw new Exception('The --limit option must be at least 1.');
        }

        $events = $this->spinupwp->listEvents($limit);

        if ($events->isEmpty()) {
            $this->warn('No events found.');
            return self::SUCCESS;
        }

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $events->transform(fn ($event) => $this->specifyFields($event));
            $this->format($events);
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $events->transform(fn ($event) => $this->specifyFields($event, [
                'id',
                'name',
                'status',
                'server_id',
                'created_at',
            ]));
        }

        $this->format($events);

        return self::SUCCESS;
    }
}
