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

            if (isset($item['ssh_publickey'])) {
                unset($item['ssh_publickey']);
            }

            if (isset($item['git_publickey'])) {
                unset($item['git_publickey']);
            }

            if (isset($item['database'])) {
                $item['database'] = $item['database']['server'];
            }

            if (isset($totalDiskSpace['disk_space'])) {
                $totalDiskSpace = number_format($item['disk_space']['total'] / 1024 / 1024 / 1024, 2);
                $usedDiskSpace = number_format($item['disk_space']['used'] / 1024 / 1024 / 1024, 2);
                $item['disk_space'] = "{$usedDiskSpace}GB/{$totalDiskSpace}GB";
            }

            return $item;
        });
    }
}
