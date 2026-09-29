<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->site = ['id' => 1, 'domain' => 'hellfishmedia.com'];

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => $this->site]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('update backup schedule', function () {
    $this->clientMock->shouldReceive('request')->once()->with('PUT', 'sites/1/backup-schedule', [
        'json' => [
            'daily_schedule' => [
                'time_of_day'      => [2, 14],
                'backup_database'  => true,
                'backup_files'     => false,
                'retention_period' => 30,
            ],
            'weekly_schedule' => [
                'day_of_week' => [],
            ],
            'monthly_schedule' => [
                'day_of_month' => [
                    ['week_of_month' => 'first', 'day_of_week' => 1],
                    ['week_of_month' => 'last', 'day_of_week' => 5],
                ],
                'time_of_day'      => 4,
                'retention_period' => null,
            ],
        ],
    ])->andReturn(new Response(200, [], json_encode(['data' => $this->site])));

    $this->artisan('sites:backups:schedule 1 --daily-times=2,14 --daily-database=true --daily-files=false --daily-retention=30 --weekly-days=none --monthly-days=first:1,last:5 --monthly-time=4 --monthly-retention=none')
        ->expectsOutput('==> Backup schedule updated.')
        ->assertExitCode(0);
});

test('update backup schedule rejects invalid monthly days', function () {
    $this->artisan('sites:backups:schedule 1 --monthly-days=fourth:1')
        ->expectsOutput('The --monthly-days option must be a comma-separated list of week:weekday pairs, for example "first:1,last:5".')
        ->assertExitCode(1);
});

test('update backup schedule requires an option', function () {
    $this->artisan('sites:backups:schedule 1')
        ->expectsOutput("At least one option must be provided. Run 'sites:backups:schedule --help' to see the available options.")
        ->assertExitCode(1);
});
