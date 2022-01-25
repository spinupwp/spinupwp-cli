<?php

namespace App\Options\Sites;

use App\Options\Option;

class SiteUser extends Option
{
    protected string $promptValue = 'Site user';

    public function getDefault()
    {
        return str_replace('.', '', $this->default);
    }
}
