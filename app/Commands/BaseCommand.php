<?php

namespace App\Commands;

use App\Commands\Concerns\InteractsWithIO;
use App\Repositories\ConfigRepository;
use App\Repositories\SpinupWpRepository;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use SpinupWp\Exceptions\ValidationException;

abstract class BaseCommand extends Command
{
    use InteractsWithIO;

    protected ConfigRepository $config;

    protected SpinupWpRepository $spinupwp;

    protected bool $requiresToken = true;

    protected string $command;

    protected array $validationLabels = [];

    public function __construct(ConfigRepository $configuration, SpinupWpRepository $spinupWp)
    {
        parent::__construct();

        $this->config   = $configuration;
        $this->spinupwp = $spinupWp;
    }

    public function handle(): int
    {
        if (method_exists($this, 'setup')) {
            $this->setup();
        }

        if ($this->requiresToken && !$this->config->isConfigured()) {
            $this->error("You must first run 'spinupwp configure' in order to set up your API token.");
            return self::FAILURE;
        }

        try {
            if (!$this->spinupwp->hasApiKey()) {
                $this->spinupwp->setClient(new Client([
                    'base_uri'    => $this->config->get('api_url', $this->profile(), 'https://api.spinupwp.app/v1/'),
                    'http_errors' => false,
                    'headers'     => [
                        'Authorization' => "Bearer {$this->apiToken()}",
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                        'User-Agent'    => 'SpinupWP/' . config('app.version'),
                    ],
                ]));
            }

            return $this->action();
        } catch (ValidationException $e) {
            $errorRows = [];
            foreach ($e->errors()['errors'] as $field => $errors) {
                $errorRows[] = [$this->applyValidationLabel($field), implode("\n", $errors)];
            }

            $this->error('Validation errors occurred.');
            $this->stepTable([
                'Field',
                'Error Message',
            ], $errorRows);

            return self::FAILURE;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function apiToken(): string
    {
        $apiToken = $this->config->get('api_token', $this->profile());

        if (!$apiToken || !is_string($apiToken)) {
            throw new Exception("The API token for the profile {$this->profile()} is not yet configured");
        }

        return $apiToken;
    }

    protected function profile(): string
    {
        if (is_string($this->option('profile'))) {
            return $this->option('profile');
        }

        return 'default';
    }

    protected function applyValidationLabel(string $key): string
    {
        if (empty($this->validationLabels) || !array_key_exists($key, $this->validationLabels)) {
            return Str::title(str_replace('_', ' ', $key));
        }

        return $this->validationLabels[$key];
    }

    abstract protected function action(): int;
}
