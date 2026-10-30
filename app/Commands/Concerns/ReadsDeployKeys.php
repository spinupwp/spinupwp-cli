<?php

namespace App\Commands\Concerns;

use Exception;

trait ReadsDeployKeys
{
    /**
     * Read a Git deploy key pair from the key file paths given in two options.
     *
     * @return array{privatekey: string, publickey: string}|null Null when neither option is given.
     */
    protected function readDeployKey(string $privateKeyOption, string $publicKeyOption): ?array
    {
        $privateKeyPath = $this->option($privateKeyOption);
        $publicKeyPath  = $this->option($publicKeyOption);

        $privateKeyPath = is_string($privateKeyPath) && $privateKeyPath !== '' ? $privateKeyPath : null;
        $publicKeyPath  = is_string($publicKeyPath) && $publicKeyPath !== '' ? $publicKeyPath : null;

        if (is_null($privateKeyPath) && is_null($publicKeyPath)) {
            return null;
        }

        if (is_null($privateKeyPath) || is_null($publicKeyPath)) {
            throw new Exception("The --{$privateKeyOption} and --{$publicKeyOption} options must be provided together.");
        }

        return [
            'privatekey' => $this->readKeyFile($privateKeyPath),
            'publickey'  => $this->readKeyFile($publicKeyPath),
        ];
    }

    protected function readKeyFile(string $path): string
    {
        $path = $this->expandHomeDirectory($path);

        if (!is_file($path) || !is_readable($path)) {
            throw new Exception("Unable to read the key file \"{$path}\".");
        }

        return trim((string) file_get_contents($path));
    }

    /**
     * Shells don't expand a tilde that follows "=" (e.g. --private-key=~/.ssh/key), so expand it here.
     */
    protected function expandHomeDirectory(string $path): string
    {
        if ($path !== '~' && !str_starts_with($path, '~/')) {
            return $path;
        }

        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: getenv('USERPROFILE');

        if (!is_string($home) || $home === '') {
            return $path;
        }

        return rtrim($home, '/\\') . substr($path, 1);
    }
}
