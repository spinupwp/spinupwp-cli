<?php

namespace App\OptionsIO\Sites;

use App\OptionsIO\Option;

class SiteUser extends Option
{
    protected string $promptValue = 'Site user';

    public function getDefault()
    {
        return str_replace('.', '', $this->default);
    }
}
