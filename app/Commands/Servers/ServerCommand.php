<?php

namespace App\Commands\Servers;

use App\Commands\BaseCommand;
use Illuminate\Support\Collection;

abstract class ServerCommand extends BaseCommand
{
    protected function filterData(Collection $data): Collection
    {
        if ($this->displayFormat() === 'json') {
            return $data;
        }

        return $data->map(function ($item) {
            $item = $item->toArray();
            unset($item['ssh_publickey']);
            unset($item['git_publickey']);
            $item['database'] = $item['database']['server'];

            $totalDiskSpace = number_format($item['disk_space']['total'] / 1024 / 1024 / 1024, 2);
            $usedDiskSpace = number_format($item['disk_space']['used'] / 1024 / 1024 / 1024, 2);
            $item['disk_space'] = "{$usedDiskSpace}GB/{$totalDiskSpace}GB";

            return $item;
        });
    }
}
