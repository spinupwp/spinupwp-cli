<?php

namespace App\Commands\Events;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;
use App\Field;

abstract class Events extends BaseCommand
{
    use SpecifyFields;

    protected function setup(): void
    {
        $this->fieldsMap = [
            (new Field('ID', 'id')),
            (new Field('Name', 'name')),
            (new Field('Status', 'status'))
                ->withFirstCharUpperCase(),
            (new Field('Server ID', 'server_id')),
            (new Field('Initiated By', 'initiated_by')),
            (new Field('Created At', 'created_at')),
            (new Field('Started At', 'started_at')),
            (new Field('Finished At', 'finished_at')),
            (new Field('Output', 'output')),
        ];
    }
}
