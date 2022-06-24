<?php

namespace App\Commands\DigitalOcean;

use App\Commands\BaseCommand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ConnectCommand extends BaseCommand
{
    private const DEFAULT_MYSQL_PWD_FILE_PATH = '/root';

    protected $signature = 'digitalocean:connect {--profile=}';

    protected $description = 'Connect a DigitalOcean 1-click SpinupWP app';

    protected bool $requiresToken = false;

    protected string $connectionToken = '';

    protected string $publicKey = '';

    protected function action(): int
    {
        $this->line('Welcome to the SpinupWP 1-click app. This wizard will allow you to connect your brand new server to your SpinupWP account');
        if (!$this->confirm('Do you want to continue?', true)) {
            return self::SUCCESS;
        }

        try {
            $this->changeMySqlPassword();
            $this->requestConnection();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function requestConnection(): void
    {
        $this->line('Connecting to spinupwp.app');
        $data = $this->prepareConnectionData();

        $response = Http::acceptJson()->post('http://spinupwp.test/api/connect/', $data);

        $this->connectionToken = $response->json()['token'];

        if (!$this->connectionToken) {
            throw new \Exception('Something went wrong. Please try again later.');
        }

        $this->line("Visit http://spinupwp.test/connect-image/{$this->connectionToken} to connect your new server to your SpinupWP account and then return to this and press Enter");

        while ($this->publicKey === '') {
            $this->ask('Press Enter to continue');
            $this->line('Fetching public key');
            $this->getPublicKey();
        }

        $this->addPublicKey();

        $this->line('Completing the connection to your server');

        $response = Http::acceptJson()->put("http://spinupwp.test/api/connect/{$this->connectionToken}");

        $this->info('Server connected. You can now manage your server from your SpinupWP account.');
    }

    protected function getPublicKey(): void
    {
        try {
            $response = Http::acceptJson()->get("http://spinupwp.test/api/connect/{$this->connectionToken}");
        } catch (\Exception $e) {
            $this->warn("Unable to fetch public key. Please ensure you completed the steps described in http://spinupwp.test/connect-image/{$this->connectionToken} and try again.");
            return;
        }

        $this->publicKey = $response->json()['public_key'];
    }

    protected function addPublicKey(): void
    {
        $this->line('Adding public key to your server');

        if ($this->config->isDevOrTesting()) {
            return;
        }

        exec("echo {$this->publicKey} /home/spinupwp/.ssh/authorized_keys", $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error(implode("\n", $output));
            throw new \Exception('Cannot add your public key.');
        }
    }

    protected function prepareConnectionData(): array
    {
        if ($this->config->isDevOrTesting()) {
            return [
                'ip_address' => '143.198.155.210',
                'name'       => 'daniel-do-test-1',
                'provider'   => 'DigitalOcean',
                'timezone'   => 'America/Mexico_City',
                'database'   => 'mysql-8',
                'ssh_port'   => 22,
            ];
        }

        exec('cat /etc/hostname', $hostname);
        exec('cat /etc/timezone', $timezone);
        exec('ip addr show | grep -E -o "(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)" | grep -E -o "([0-9]{3}[\.]){3}[0-9]{3}" | grep -P -v "^([0-9]{3}[\.]){3}255"', $ip);

        return [
            'ip_address' => $ip,
            'name'       => $hostname,
            'provider'   => 'DigitalOcean',
            'timezone'   => $timezone,
            'database'   => 'mysql-8', //TODO: get from config,
            'ssh_port'   => 22,
        ];
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

        // $defaultRootPassword = $this->readDefaultRootPassword();

        // if (empty($defaultRootPassword)) {
        //     throw new \Exception('Cannot change MySQL root password.');
        // }

        // $changeMySqlPasswordCommand = 'mysql -u root -p' . $defaultRootPassword . ' -e \'ALTER USER "root"@"localhost" IDENTIFIED BY "' . $mysqlPassword . '"\'';

        $changeMySqlPasswordCommand = 'mysql -e \'ALTER USER "root"@"localhost" IDENTIFIED BY "' . $mysqlPassword . '"\'';

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
            : self::DEFAULT_MYSQL_PWD_FILE_PATH;

        $path .= '/.my.cnf';

        $defaultRootPassword = file_get_contents($path);

        return preg_match('/^password=\"(.*)\"/', $defaultRootPassword, $matches)
            ? $matches[1]
            : '';

        if (empty($defaultRootPassword)) {
            throw new \Exception('Cannot change MySQL root password.');
        }

        return $defaultRootPassword;
    }
}
