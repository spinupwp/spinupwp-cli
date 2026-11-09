<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'ssh-keys?page=1&limit=100', [])->andReturn(
        new Response(200, [], listResponseJson([
            ['id' => 3, 'name' => 'CI deploy'],
            ['id' => 4, 'name' => 'Engineer'],
            ['id' => 5, 'name' => 'Contractor'],
        ]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

function siteWithSshKeys(array $sshKeys, string $userAuth = 'publickey'): Response
{
    return new Response(200, [], json_encode(['data' => [
        'id'        => 1,
        'domain'    => 'hellfish.media',
        'user_auth' => $userAuth,
        'ssh_keys'  => $sshKeys,
    ]]));
}

test('add an ssh key chosen by name', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(siteWithSshKeys([['id' => 3, 'name' => 'CI deploy']]));
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/site-user/ssh-keys', [
        'json' => ['ssh_key_id' => 4],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:ssh-keys:add 1 engineer')
        ->expectsOutput('==> SSH key "Engineer" queued for adding to the site user.')
        ->assertExitCode(0);
});

test('add an ssh key chosen interactively only offers the keys not yet authorized', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(siteWithSshKeys([['id' => 3, 'name' => 'CI deploy']]));
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/site-user/ssh-keys', [
        'json' => ['ssh_key_id' => 5],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => 100])));

    $this->artisan('sites:ssh-keys:add 1')
        ->expectsChoice('Which SSH key would you like to add', 5, [4 => 'Engineer', 5 => 'Contractor'])
        ->expectsOutput('==> SSH key "Contractor" queued for adding to the site user.')
        ->assertExitCode(0);
});

test('add an ssh key that is already authorized', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(siteWithSshKeys([['id' => 3, 'name' => 'CI deploy']]));
    $this->clientMock->shouldReceive('request')->once()->with('POST', 'sites/1/site-user/ssh-keys', [
        'json' => ['ssh_key_id' => 3],
    ])->andReturn(new Response(200, [], json_encode(['event_id' => null])));

    $this->artisan('sites:ssh-keys:add 1 3')
        ->expectsOutput('==> SSH key "CI deploy" is already authorized for the site user.')
        ->assertExitCode(0);
});

test('add an ssh key when every team key is already authorized', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(siteWithSshKeys([
        ['id' => 3, 'name' => 'CI deploy'],
        ['id' => 4, 'name' => 'Engineer'],
        ['id' => 5, 'name' => 'Contractor'],
    ]));
    $this->clientMock->shouldNotReceive('request')->with('POST', 'sites/1/site-user/ssh-keys', Mockery::any());

    $this->artisan('sites:ssh-keys:add 1')
        ->expectsOutput("All of your team's SSH keys are already authorized for the site user.")
        ->assertExitCode(0);
});

test('add an ssh key that is not on the team', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(siteWithSshKeys([]));
    $this->clientMock->shouldNotReceive('request')->with('POST', 'sites/1/site-user/ssh-keys', Mockery::any());

    $this->artisan('sites:ssh-keys:add 1 Laptop')
        ->expectsOutput('No SSH key "Laptop" found on your team.')
        ->assertExitCode(1);
});

test('add an ssh key to a site user that uses password authentication', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(siteWithSshKeys([], 'password'));
    $this->clientMock->shouldNotReceive('request')->with('POST', 'sites/1/site-user/ssh-keys', Mockery::any());

    $this->artisan('sites:ssh-keys:add 1 4')
        ->expectsOutput('The site user on "hellfish.media" uses password authentication. Switch it to SSH keys with sites:site-user first.')
        ->assertExitCode(1);
});
