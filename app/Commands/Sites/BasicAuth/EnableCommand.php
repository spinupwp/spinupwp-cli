<?php

namespace App\Commands\Sites\BasicAuth;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Questions\Ask;
use App\Questions\HasQuestions;
use App\Questions\Question;

class EnableCommand extends BaseCommand
{
    use HasQuestions;
    use HasSiteIdParameter;

    protected $signature = 'sites:basic-auth:enable
                            {site_id? : The site to enable basic auth for}
                            {--username= : The basic auth username}
                            {--password= : The basic auth password}
                            {--f|force : Run without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Enable HTTP basic authentication for a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to enable basic auth for', fn ($site) => !$site->basic_auth['enabled'], 'There are no sites with basic auth disabled.');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $answers = $this->askQuestions($this->nonInteractive());

        $eventId = $site->enableBasicAuth([
            'username' => $answers['username'],
            'password' => $answers['password'],
        ]);

        $this->displaySiteEvent($site, $eventId, 'Basic auth queued for enabling.');

        return self::SUCCESS;
    }

    /**
     * @return Question[]
     */
    public function questions(): array
    {
        return [
            Ask::make('Username')
                ->withFlag('username'),

            Ask::make('Password')
                ->withFlag('password'),
        ];
    }
}
