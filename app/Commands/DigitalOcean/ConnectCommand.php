<?php

namespace App\Commands\DigitalOcean;

use App\Commands\BaseCommand;
use Illuminate\Support\Str;

class ConnectCommand extends BaseCommand
{
    private const DEFAULT_MYSQL_PWD_FILE = '/root/mysqlpwd';

    protected $signature = 'digitalocean:connect {--profile=}';

    protected $description = 'Connect a DigitalOcean 1-click SpinupWP app';

    protected bool $requiresToken = false;

    protected function action(): int
    {
        $this->line('Welcome to the SpinupWP 1-click app. This wizard will allow you to connect your brand new server to your SpinupWP account');
        if (!$this->confirm('Do you want to continue?', true)) {
            return self::SUCCESS;
        }

        try {
            $this->changeMySqlPassword();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function changeMySqlPassword(): void
    {
        $this->line('Database Root Password');
        $mysqlPassword = '';

        if ($this->confirm('Do you want to generate a random password?', true)) {
            $mysqlPassword = Str::random(24);
        }

        while (empty($mysqlPassword)) {
            $mysqlPassword = $this->ask('Enter a new MySQL root password');
        }

        $this->newLine();

        $this->line("Your MySQL root password: {$mysqlPassword}");

        $this->warn("Make sure to note this password down somewhere (or save it in a Password Manager) as we won't show it again.");

        $this->ask('Press Enter to continue');

        $this->line('Changing MySQL root password');

        $defaultRootPassword = $this->readDefaultRootPassword();

        if (empty($defaultRootPassword)) {
            throw new \Exception('Cannot change MySQL root password.');
        }

        $changeMySqlPasswordCommand = 'mysql -u root -p' . $defaultRootPassword . ' -e \'ALTER USER "root"@"localhost" IDENTIFIED BY "' . $mysqlPassword . '"\'';

        if (!$this->config->isDevOrTesting()) {
            exec($changeMySqlPasswordCommand, $output, $exitCode);
            if ($exitCode !== 0) {
                $this->error(implode("\n", $output));
                throw new \Exception('Cannot change MySQL root password.');
            }
        }

        $this->info('MySQL root password changed.');
    }

    protected function readDefaultRootPassword(): string
    {
        $path = $this->config->isDevOrTesting()
            ? base_path('tests')
            : self::DEFAULT_MYSQL_PWD_FILE;

        $path .= '/mysqlpwd';

        $defaultRootPassword = file_get_contents($path);

        if (empty($defaultRootPassword)) {
            throw new \Exception('Cannot change MySQL root password.');
        }

        return $defaultRootPassword;
    }
}
