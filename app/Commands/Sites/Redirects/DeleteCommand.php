<?php

namespace App\Commands\Sites\Redirects;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;
use Exception;
use SpinupWp\Resources\Site;

class DeleteCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected $signature = 'sites:redirects:delete
                            {site_id? : The site to delete a path redirect from}
                            {--from= : The path the redirect redirects from}
                            {--to= : The path or URL the redirect redirects to}
                            {--f|force : Delete the path redirect without prompting for confirmation}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Delete a path redirect from a site';

    protected function action(): int
    {
        $site = $this->selectSite('Which site would you like to delete a path redirect from');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $redirect = $this->selectRedirect($site);

        if (is_null($redirect)) {
            return self::SUCCESS;
        }

        if (!$this->forceOrConfirm("Are you sure you want to delete the redirect from \"{$redirect['from']}\" to \"{$redirect['to']}\"?", false)) {
            return self::SUCCESS;
        }

        $eventId = $site->deletePathRedirect($redirect);

        $this->displaySiteEvent($site, $eventId, 'Path redirect queued for deletion.');

        return self::SUCCESS;
    }

    protected function selectRedirect(Site $site): ?array
    {
        $from = $this->stringOption('from');
        $to   = $this->stringOption('to');

        if (!is_null($from) && !is_null($to)) {
            return ['from' => $from, 'to' => $to];
        }

        if (!is_null($from) || !is_null($to)) {
            throw new Exception('The --from and --to options must be provided together.');
        }

        $redirects = $this->spinupwp->listPathRedirects($site->id)->values();

        if ($redirects->isEmpty()) {
            $this->warn('No path redirects found.');
            return null;
        }

        $selected = $this->askToSelect(
            'Which path redirect would you like to delete',
            $redirects->map(fn ($redirect) => "{$redirect->from} → {$redirect->to}")->toArray(),
        );

        $redirect = $redirects->get($selected);

        return ['from' => $redirect->from, 'to' => $redirect->to];
    }
}
