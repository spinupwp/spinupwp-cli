<?php

namespace App\Commands\SshKey;

use App\Commands\BaseCommand;
use App\Commands\Concerns\SpecifyFields;
use App\Field;

abstract class SshKeys extends BaseCommand
{
    use SpecifyFields;

    protected function setup(): void
    {
        $this->fieldsMap = [
            (new Field('ID', 'id')),
            (new Field('Name', 'name')),
            (new Field('Fingerprint', 'fingerprint')),
            (new Field('Public Key', 'publickey')),
            (new Field('Created At', 'created_at')),
        ];
    }
}
