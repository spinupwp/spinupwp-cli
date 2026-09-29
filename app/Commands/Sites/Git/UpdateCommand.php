<?php

namespace App\Commands\Sites\Git;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;

class UpdateCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected $signature = 'sites:git:update
                            {site_id? : The site to update}
                            {--repo= : The Git repository URL}
                            {--branch= : The Git branch to deploy from}
                            {--deploy-script= : The script that will run after each Git deployment}
                            {--push-to-deploy= : Enable or disable push to deploy (true or false)}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Update the Git settings for a site';

    protected function action(): int
    {
        $payload = $this->withoutNulls([
            'repo'           => $this->stringOption('repo'),
            'branch'         => $this->stringOption('branch'),
            'deploy_script'  => $this->stringOption('deploy-script'),
            'push_to_deploy' => $this->boolOption('push-to-deploy'),
        ]);

        $this->ensureOptionsProvided($payload);

        $site = $this->selectSite('Which site would you like to update the Git settings for', fn ($site) => $site->git['enabled'], 'There are no sites with Git enabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventId = $site->updateGit($payload);

        $this->displaySiteEvent($site, $eventId, 'Git settings queued for update.', 'Git settings updated.');

        return self::SUCCESS;
    }
}
