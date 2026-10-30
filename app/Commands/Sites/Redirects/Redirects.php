<?php

namespace App\Commands\Sites\Redirects;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\SpecifyFields;
use App\Field;

abstract class Redirects extends BaseCommand
{
    use HasSiteIdParameter;
    use SpecifyFields;

    protected function setup(): void
    {
        $this->fieldsMap = [
            (new Field('From', 'from')),
            (new Field('To', 'to')),
            (new Field('Type', 'type'))
                ->withTransformRule(fn ($value) => $value === 'permanent' ? 'Permanent (301)' : 'Temporary (302)'),
            (new Field('Created At', 'created_at')),
        ];
    }
}
