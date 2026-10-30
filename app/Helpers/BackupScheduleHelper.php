<?php

namespace App\Helpers;

class BackupScheduleHelper
{
    public const FREQUENCIES = ['daily', 'weekly', 'monthly'];

    protected const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    /**
     * Describe one frequency of a site's backups.schedule, e.g. "Monday at 03:00, files and database, kept 14 days".
     */
    public static function describe(string $frequency, mixed $schedule): string
    {
        $schedule = is_array($schedule) ? $schedule : [];
        $when     = self::when($frequency, $schedule);

        if (is_null($when)) {
            return 'Disabled';
        }

        return implode(', ', [
            $when,
            self::contents($schedule),
            self::retention($schedule),
        ]);
    }

    /**
     * Describe every frequency of a site's backups.schedule as [frequency, description] rows.
     *
     * @return array<int, array{string, string}>
     */
    public static function rows(mixed $schedule): array
    {
        $schedule = is_array($schedule) ? $schedule : [];

        return array_map(
            fn (string $frequency) => [ucfirst($frequency), self::describe($frequency, $schedule[$frequency] ?? null)],
            self::FREQUENCIES,
        );
    }

    /**
     * @param array<mixed> $schedule
     */
    protected static function when(string $frequency, array $schedule): ?string
    {
        if ($frequency === 'daily') {
            $hours = array_map(fn ($hour) => self::hour($hour), self::listOf($schedule['time_of_day'] ?? null));

            return empty($hours) ? null : self::joinList($hours);
        }

        if ($frequency === 'weekly') {
            $days = array_map(fn ($day) => self::weekday($day), self::listOf($schedule['day_of_week'] ?? null));
        } else {
            $days = array_map(function ($day) {
                $day = is_array($day) ? $day : [];

                return trim(self::stringOf($day['week_of_month'] ?? null) . ' ' . self::weekday($day['day_of_week'] ?? null));
            }, self::listOf($schedule['day_of_month'] ?? null));
        }

        if (empty($days)) {
            return null;
        }

        $days = ucfirst(self::joinList($days));

        if (!isset($schedule['time_of_day']) || !is_numeric($schedule['time_of_day'])) {
            return $days;
        }

        return $days . ' at ' . self::hour($schedule['time_of_day']);
    }

    /**
     * @param array<mixed> $schedule
     */
    protected static function contents(array $schedule): string
    {
        $contents = array_keys(array_filter([
            'files'    => !empty($schedule['backup_files']),
            'database' => !empty($schedule['backup_database']),
        ]));

        return empty($contents) ? 'nothing selected' : implode(' and ', $contents);
    }

    /**
     * @param array<mixed> $schedule
     */
    protected static function retention(array $schedule): string
    {
        $days = $schedule['retention_period'] ?? null;

        if (!is_numeric($days)) {
            return 'no retention limit';
        }

        $days = (int) $days;

        return "kept {$days} " . ($days === 1 ? 'day' : 'days');
    }

    protected static function hour(mixed $hour): string
    {
        return sprintf('%02d:00', is_numeric($hour) ? (int) $hour : 0);
    }

    protected static function weekday(mixed $day): string
    {
        if (!is_numeric($day)) {
            return self::stringOf($day);
        }

        return self::WEEKDAYS[(int) $day] ?? (string) $day;
    }

    /**
     * @return array<int, mixed>
     */
    protected static function listOf(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    protected static function stringOf(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param array<int, string> $items
     */
    protected static function joinList(array $items): string
    {
        if (count($items) <= 1) {
            return implode('', $items);
        }

        $last = array_pop($items);

        return implode(', ', $items) . ' and ' . $last;
    }
}
