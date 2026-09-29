<?php

namespace App\Commands\Sites;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;

class NginxCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected $signature = 'sites:nginx
                            {site_id? : The site to update}
                            {--uploads-directory-protected= : Disallow PHP execution in the uploads folder (true or false)}
                            {--xmlrpc-protected= : Disable xmlrpc.php (true or false)}
                            {--subdirectory-rewrite= : Enable rewrite rules for WordPress Multisite subdirectory installs (true or false)}
                            {--public-folder= : The public folder, relative to the site\'s files directory}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Update the Nginx settings for a site';

    protected function action(): int
    {
        $payload = $this->withoutNulls([
            'uploads_directory_protected'   => $this->boolOption('uploads-directory-protected'),
            'xmlrpc_protected'              => $this->boolOption('xmlrpc-protected'),
            'subdirectory_rewrite_in_place' => $this->boolOption('subdirectory-rewrite'),
            'public_folder'                 => $this->stringOption('public-folder'),
        ]);

        $this->ensureOptionsProvided($payload);

        $site = $this->selectSite('Which site would you like to update the Nginx settings for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $eventIds = $site->updateNginx($payload);

        if (empty($eventIds)) {
            $this->successfulStep('Nginx settings are already up to date.');
            return self::SUCCESS;
        }

        $this->successfulStep('Nginx settings queued for update.');

        $this->stepTable([
            'Event ID',
            'Site',
        ], array_map(fn ($eventId) => [$eventId, $site->domain], $eventIds));

        return self::SUCCESS;
    }
}
