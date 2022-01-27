<?php

namespace App\Commands\Concerns;

use Illuminate\Support\Collection;

trait SelectsServer
{
    public function selectServer(string $action): Collection
    {
        $serverId = $this->argument('server_id');

        if (empty($serverId) && !$this->nonInteractive()) {
            $serverId = $this->askToSelectServer("Which server would you like to $action");
        }

        $server = $this->spinupwp->getServer((int) $serverId);

        if ($this->forceOrConfirm("Are you sure you want to $action \"{$server->name}\"?")) {
            return collect([$server]);
        }

        return collect();
    }

    public function askToSelectServer(string $question): int
    {
        $choices = collect($this->spinupwp->listServers());

        return $this->askToSelect(
            $question,
            $choices->keyBy('id')->map(fn ($server) => $server->name)->toArray()
        );
    }
}
