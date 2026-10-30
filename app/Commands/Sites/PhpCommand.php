<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Helpers\OptionsHelper;
use App\Questions\Choice;

class PhpCommand extends BaseCommand
{
    use HasSiteIdParameter;

    protected $signature = 'sites:php
                            {site_id? : The site to change the PHP version for}
                            {--php-version= : The PHP version the site should run under}
                            {--f|force : Run without prompting}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = "Change a site's PHP version";

    protected array $validationLabels = [
        'php_version' => 'PHP Version',
    ];

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to change the PHP version for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $versions = array_values(array_diff(OptionsHelper::PHP_VERSIONS, [$site->php_version]));

        $phpVersion = Choice::make("Which PHP version should \"{$site->domain}\" run under (currently {$site->php_version})?")
            ->withFlag('php-version')
            ->withChoices($versions)
            ->withDefault(in_array(OptionsHelper::DEFAULT_PHP_VERSION, $versions, true) ? OptionsHelper::DEFAULT_PHP_VERSION : $versions[0])
            ->nonInteractive($this->nonInteractive())
            ->resolveAnswer($this);

        $eventId = $site->updatePhpSettings(['php_version' => $phpVersion]);

        $this->displaySiteEvent($site, $eventId, "PHP {$phpVersion} queued for \"{$site->domain}\".");

        return self::SUCCESS;
    }
}
