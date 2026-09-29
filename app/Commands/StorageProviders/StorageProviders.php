<?php

namespace App\Commands\StorageProviders;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;
use App\Field;

abstract class StorageProviders extends BaseCommand
{
    use SpecifyFields;

    protected function setup(): void
    {
        $this->fieldsMap = [
            (new Field('ID', 'id')),
            (new Field('Name', 'name')),
            (new Field('Service', 'service')),
            (new Field('Created At', 'created_at')),
        ];
    }
}
