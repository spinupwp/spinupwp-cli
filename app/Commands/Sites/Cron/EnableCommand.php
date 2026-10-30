<?php

namespace App\Commands\Sites\Cron;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Questions\Ask;
use App\Questions\HasQuestions;
use App\Questions\Question;

class EnableCommand extends BaseCommand
{
    use HasQuestions;
    use HasSiteIdParameter;

    protected $signature = 'sites:cron:enable
                            {site_id? : The site to enable the WP cron for}
                            {--interval= : The cron interval in minutes (1, 2, 5, 10, 15, 30, or 60)}
                            {--f|force : Run without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Enable the server-level WordPress cron for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to enable the WP cron for', fn ($site) => !$site->cron['enabled'], 'There are no sites with WP cron disabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $answers = $this->askQuestions($this->nonInteractive());

        $eventId = $site->enableCron(['interval' => (int) $answers['interval']]);

        $this->displaySiteEvent($site, $eventId, 'WP cron queued for enabling.');

        return self::SUCCESS;
    }

    /**
     * @return Question[]
     */
    public function questions(): array
    {
        return [
            Ask::make('How often should the WP cron run (1, 2, 5, 10, 15, 30, or 60 minutes)?')
                ->withFlag('interval')
                ->withDefault('5'),
        ];
    }
}
