<?php

namespace App\Commands\Sites\Backups;

use App\Commands\BaseCommand;
use App\Commands\Concerns\HasSiteIdParameter;
use App\Commands\Concerns\ParsesOptions;
use App\Helpers\BackupScheduleHelper;
use Exception;

class ScheduleCommand extends BaseCommand
{
    use HasSiteIdParameter;
    use ParsesOptions;

    protected const WEEKS_OF_MONTH = ['first', 'second', 'third', 'last'];

    protected $signature = 'sites:backups:schedule
                            {site_id? : The site to update}
                            {--daily-times= : Comma-separated hours (0-23) when daily backups run, or "none" to disable}
                            {--daily-database= : Back up the database daily (true or false)}
                            {--daily-files= : Back up site files daily (true or false)}
                            {--daily-retention= : Days to retain daily backups, or "none"}
                            {--weekly-days= : Comma-separated weekdays (0 = Sunday to 6 = Saturday) when weekly backups run, or "none" to disable}
                            {--weekly-time= : The hour (0-23) at which weekly backups run}
                            {--weekly-database= : Back up the database weekly (true or false)}
                            {--weekly-files= : Back up site files weekly (true or false)}
                            {--weekly-retention= : Days to retain weekly backups, or "none"}
                            {--monthly-days= : Comma-separated week:weekday pairs (e.g. first:1,last:5) when monthly backups run, or "none" to disable}
                            {--monthly-time= : The hour (0-23) at which monthly backups run}
                            {--monthly-database= : Back up the database monthly (true or false)}
                            {--monthly-files= : Back up site files monthly (true or false)}
                            {--monthly-retention= : Days to retain monthly backups, or "none"}
                            {--profile= : The SpinupWP configuration profile to use}';

    protected $description = 'Update the backup schedule for a site';

    protected function action(): int
    {
        $payload = array_filter([
            'daily_schedule' => $this->schedulePayload('daily', [
                'time_of_day' => $this->intListOption('daily-times'),
            ]),
            'weekly_schedule' => $this->schedulePayload('weekly', [
                'day_of_week' => $this->intListOption('weekly-days'),
                'time_of_day' => $this->intOption('weekly-time'),
            ]),
            'monthly_schedule' => $this->schedulePayload('monthly', [
                'day_of_month' => $this->monthlyDaysOption(),
                'time_of_day'  => $this->intOption('monthly-time'),
            ]),
        ]);

        $this->ensureOptionsProvided($payload);

        $site = $this->selectSite('Which site would you like to update the backup schedule for');

        if (is_null($site)) {
            return self::SUCCESS;
        }

        $site = $site->updateBackupSchedule($payload);

        $this->successfulStep('Backup schedule updated.');

        $backups = $site->backups;

        if (is_array($backups) && is_array($backups['schedule'] ?? null)) {
            $this->stepTable(['Frequency', 'Schedule'], BackupScheduleHelper::rows($backups['schedule']));
            $this->line('    Times are in the server\'s timezone.');
        }

        return self::SUCCESS;
    }

    protected function schedulePayload(string $frequency, array $timing): array
    {
        $schedule = $this->withoutNulls(array_merge($timing, [
            'backup_database' => $this->boolOption("{$frequency}-database"),
            'backup_files'    => $this->boolOption("{$frequency}-files"),
        ]));

        if ($this->stringOption("{$frequency}-retention") === 'none') {
            $schedule['retention_period'] = null;
        } elseif (!is_null($retention = $this->intOption("{$frequency}-retention"))) {
            $schedule['retention_period'] = $retention;
        }

        return $schedule;
    }

    protected function monthlyDaysOption(): ?array
    {
        $value = $this->stringOption('monthly-days');

        if (is_null($value)) {
            return null;
        }

        if ($value === 'none') {
            return [];
        }

        return array_map(function ($pair) {
            $parts = explode(':', trim($pair));

            if (count($parts) !== 2 || !in_array($parts[0], self::WEEKS_OF_MONTH, true) || !ctype_digit($parts[1])) {
                throw new Exception('The --monthly-days option must be a comma-separated list of week:weekday pairs, for example "first:1,last:5".');
            }

            return [
                'week_of_month' => $parts[0],
                'day_of_week'   => (int) $parts[1],
            ];
        }, explode(',', $value));
    }
}
