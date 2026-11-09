<?php

use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    setTestConfigFile();

    $this->clientMock->shouldReceive('request')->with('GET', 'sites/1', [])->andReturn(
        new Response(200, [], json_encode(['data' => [
            'id'        => 1,
            'domain'    => 'hellfish.media',
            'user_auth' => 'publickey',
            'ssh_keys'  => [
                ['id' => 3, 'name' => 'CI deploy', 'fingerprint' => 'aa:bb'],
                ['id' => 4, 'name' => 'Engineer', 'fingerprint' => 'cc:dd'],
            ],
        ]]))
    );
});

afterEach(function () {
    deleteTestConfigFile();
});

test('remove an ssh key chosen by name', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/site-user/ssh-keys/4', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:ssh-keys:remove 1 Engineer')
        ->expectsConfirmation('Are you sure you want to remove "Engineer" from the site user on "hellfish.media"?', 'yes')
        ->expectsOutput('==> SSH key "Engineer" queued for removal from the site user.')
        ->assertExitCode(0);
});

test('remove an ssh key chosen interactively', function () {
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/1/site-user/ssh-keys/3', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:ssh-keys:remove 1 --force')
        ->expectsChoice('Which SSH key would you like to remove', 3, [3 => 'CI deploy', 4 => 'Engineer'])
        ->expectsOutput('==> SSH key "CI deploy" queued for removal from the site user.')
        ->assertExitCode(0);
});

test('remove an ssh key that is not authorized for the site user', function () {
    $this->clientMock->shouldNotReceive('request')->with('DELETE', Mockery::any(), Mockery::any());

    $this->artisan('sites:ssh-keys:remove 1 5 --force')
        ->expectsOutput('No SSH key "5" is authorized for the site user on "hellfish.media".')
        ->assertExitCode(1);
});

test('remove an ssh key does nothing when not confirmed', function () {
    $this->clientMock->shouldNotReceive('request')->with('DELETE', Mockery::any(), Mockery::any());

    $this->artisan('sites:ssh-keys:remove 1 4')
        ->expectsConfirmation('Are you sure you want to remove "Engineer" from the site user on "hellfish.media"?', 'no')
        ->assertExitCode(0);
});

test('remove an ssh key by id when another key is named like that id', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/2', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 2, 'domain' => 'hellfish.media', 'user_auth' => 'publickey', 'ssh_keys' => [
            ['id' => 3, 'name' => '4', 'fingerprint' => 'aa:bb'],
            ['id' => 4, 'name' => 'Engineer', 'fingerprint' => 'cc:dd'],
        ]]]))
    );
    $this->clientMock->shouldReceive('request')->once()->with('DELETE', 'sites/2/site-user/ssh-keys/4', [])->andReturn(
        new Response(200, [], json_encode(['event_id' => 100]))
    );

    $this->artisan('sites:ssh-keys:remove 2 4 --force')
        ->expectsOutput('==> SSH key "Engineer" queued for removal from the site user.')
        ->assertExitCode(0);
});

test('remove an ssh key refuses a name shared by more than one key', function () {
    $this->clientMock->shouldReceive('request')->with('GET', 'sites/2', [])->andReturn(
        new Response(200, [], json_encode(['data' => ['id' => 2, 'domain' => 'hellfish.media', 'user_auth' => 'publickey', 'ssh_keys' => [
            ['id' => 3, 'name' => 'deploy', 'fingerprint' => 'aa:bb'],
            ['id' => 4, 'name' => 'Deploy', 'fingerprint' => 'cc:dd'],
        ]]]))
    );
    $this->clientMock->shouldNotReceive('request')->with('DELETE', Mockery::any(), Mockery::any());

    $this->artisan('sites:ssh-keys:remove 2 deploy --force')
        ->expectsOutput('More than one SSH key is named "deploy". Use its ID instead.')
        ->assertExitCode(1);
});
