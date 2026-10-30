<?php

namespace App\Commands\Events;

class GetCommand extends Events
{
    protected $signature = 'events:get
                            {event_id : The event to output}
                            {--fields= : The fields to output}
                            {--format= : The output format (json or table)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Get an event, including its status and output';

    public function action(): int
    {
        $this->largeOutput = true;
        $event             = $this->spinupwp->events->get((int) $this->argument('event_id'));

        if ($this->shouldSpecifyFields()) {
            $this->saveFieldsFilter();
            $this->format($this->specifyFields($event));
            return self::SUCCESS;
        }

        if ($this->displayFormat() === 'table') {
            $event = $this->specifyFields($event, [
                'id',
                'name',
                'status',
                'server_id',
                'initiated_by',
                'created_at',
                'started_at',
                'finished_at',
                'output',
            ]);
        }

        $this->format($event);

        return self::SUCCESS;
    }
}
