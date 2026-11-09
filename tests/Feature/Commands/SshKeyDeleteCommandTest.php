<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->teamKeys = function (array $keys) {
        $this->clientMock->shouldReceive('request')->with('GET', 'ssh-keys?page=1&limit=100', [])->andReturn(
            new Response(200, [], listResponseJson($keys))
        );
    };
});

afterEach(function () {
    deleteTestConfigFile();
});

test('delete an ssh key chosen by name', function () {
    ($this->teamKeys)([['id' => 3, 'name' => 'CI deploy'], ['id' => 7, 'name' => 'Contractor']]);
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'ssh-keys/7', [])->andReturn(
        new Response(200, [], json_encode(['event_ids' => [21, 22]]))
    );

    $this->artisan('ssh-key:delete contractor')
        ->expectsConfirmation('Are you sure you want to delete "Contractor"? It will also be removed from every site user and sudo user that has it.', 'yes')
        ->expectsOutput('==> SSH key "Contractor" deleted and queued for removal from the site users and sudo users that had it.')
        ->assertExitCode(0);
});

test('delete an ssh key chosen interactively that is not in use', function () {
    ($this->teamKeys)([['id' => 3, 'name' => 'CI deploy'], ['id' => 7, 'name' => 'Contractor']]);
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'ssh-keys/3', [])->andReturn(
        new Response(200, [], json_encode(['event_ids' => []]))
    );

    $this->artisan('ssh-key:delete --force')
        ->expectsChoice('Which SSH key would you like to delete', 3, [3 => 'CI deploy', 7 => 'Contractor'])
        ->expectsOutput('==> SSH key "CI deploy" deleted.')
        ->assertExitCode(0);
});

test('delete an ssh key that is not on the team', function () {
    ($this->teamKeys)([['id' => 3, 'name' => 'CI deploy'], ['id' => 7, 'name' => 'Contractor']]);
    $this->clientMock->shouldNotReceive('request')->with('DELETE', Mockery::any(), Mockery::any());

    $this->artisan('ssh-key:delete 99 --force')
        ->expectsOutput('No SSH key "99" found on your team.')
        ->assertExitCode(1);
});

test('delete an ssh key does nothing when not confirmed', function () {
    ($this->teamKeys)([['id' => 3, 'name' => 'CI deploy'], ['id' => 7, 'name' => 'Contractor']]);
    $this->clientMock->shouldNotReceive('request')->with('DELETE', Mockery::any(), Mockery::any());

    $this->artisan('ssh-key:delete 7')
        ->expectsConfirmation('Are you sure you want to delete "Contractor"? It will also be removed from every site user and sudo user that has it.', 'no')
        ->assertExitCode(0);
});

test('delete an ssh key refuses a name shared by more than one key', function () {
    ($this->teamKeys)([['id' => 3, 'name' => 'CI deploy'], ['id' => 7, 'name' => 'Contractor'], ['id' => 8, 'name' => 'contractor']]);
    $this->clientMock->shouldNotReceive('request')->with('DELETE', Mockery::any(), Mockery::any());

    $this->artisan('ssh-key:delete contractor --force')
        ->expectsOutput('More than one SSH key is named "contractor". Use its ID instead.')
        ->assertExitCode(1);
});
