<?php

use App\Commands\BaseCommand;

beforeEach(function () {
    deleteTestConfigFile();
});

afterEach(function () {
    //
});


test('isConfigured method', function () {

    $baseCommand = new BaseCommand();
    $isConfigured = $baseCommand->isConfigured();
    expect($isConfigured)->toBeFalse();
    setTestConfigFile();
    $isConfigured = $baseCommand->isConfigured();
    expect($isConfigured)->toBeTrue();
});

test('get method', function () {

    $baseCommand = new BaseCommand();
    setTestConfigFile();
    expect($baseCommand->get('api_token'))->toEqual('myapikey123');
});

test('setCredentials method', function () {
    $baseCommand = new BaseCommand();
    // first time
    $baseCommand->saveConfig('mynewapitoken', 'json');
    expect($baseCommand->get('api_token'))->toEqual('mynewapitoken');
    // multiple teams
    $baseCommand->saveConfig('myteamapitoken', 'json', "team1");
    expect($baseCommand->get('api_token', 'team1'))->toEqual('myteamapitoken');
    //overwrite existing team apitoken
    $baseCommand->saveConfig('mynewteamapitoken', 'json', "team1");
    expect($baseCommand->get('api_token', "team1"))->toEqual('mynewteamapitoken');
});
