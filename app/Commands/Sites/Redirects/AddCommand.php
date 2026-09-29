<?php

namespace App\Commands\Sites\Redirects;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Helpers\OptionsHelper;
use App\Questions\Ask;
use App\Questions\Choice;
use App\Questions\HasQuestions;
use App\Questions\Question;

class AddCommand extends BaseCommand
{
    use HasQuestions;
    use HasSiteIdParameter;

    protected $signature = 'sites:redirects:add
                            {site_id? : The site to add a path redirect to}
                            {--from= : The path to redirect from}
                            {--to= : The path or URL to redirect to}
                            {--type= : The redirect type (permanent or redirect)}
                            {--f|force : Run without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Add a path redirect to a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to add a path redirect to');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $answers = $this->askQuestions($this->nonInteractive());

        $eventId = $site->addPathRedirect([
            'from' => $answers['from'],
            'to'   => $answers['to'],
            'type' => $answers['type'],
        ]);

        $this->displaySiteEvent($site, $eventId, 'Path redirect queued for creation.');

        return self::SUCCESS;
    }

    /**
     * @return Question[]
     */
    public function questions(): array
    {
        return [
            Ask::make('Redirect from')
                ->withFlag('from'),

            Ask::make('Redirect to')
                ->withFlag('to'),

            Choice::make('Redirect type')
                ->withFlag('type')
                ->withChoices(OptionsHelper::REDIRECT_TYPES)
                ->withDefault('permanent'),
        ];
    }
}
