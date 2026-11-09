<?php

namespace App\Commands\Sites\SshKeys;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use Exception;
use Illuminate\Support\Collection;
use SpinupWp\Resources\Site;

abstract class SshKeys extends BaseCommand
{
    use HasSiteIdParameter;

    /**
     * The SSH keys authorized for the site user.
     *
     * @return Collection<int, array{id: int, name: string, fingerprint: string}>
     */
    protected function siteUserSshKeys(Site $site): Collection
    {
        return collect($site->ssh_keys ?? []);
    }

    /**
     * Find the SSH key named by the ssh_key argument (an ID or name), or ask which one.
     *
     * @template TKey of array{id: int, name: string, ...}
     *
     * @param  Collection<array-key, TKey>  $keys
     * @return TKey
     */
    protected function selectSshKey(Collection $keys, string $question, string $notFoundMessage): array
    {
        $argument = $this->argument('ssh_key');

        if (is_string($argument) && $argument !== '') {
            // An ID wins over a name, and names aren't unique, so refuse to guess between keys with the same name.
            $key = $keys->first(fn ($key) => (string) $key['id'] === $argument);

            if (is_null($key)) {
                $named = $keys->filter(fn ($key) => strcasecmp($key['name'], $argument) === 0);

                if ($named->count() > 1) {
                    throw new Exception("More than one SSH key is named \"{$argument}\". Use its ID instead.");
                }

                $key = $named->first();
            }

            if (is_null($key)) {
                throw new Exception(sprintf($notFoundMessage, $argument));
            }

            return $key;
        }

        $selected = $this->askToSelect($question, $keys->keyBy('id')->map(fn ($key) => $key['name'])->all());

        return $keys->firstOrFail(fn ($key) => $key['id'] === $selected);
    }
}
