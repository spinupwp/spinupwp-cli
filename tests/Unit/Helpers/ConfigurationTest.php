<?php

use App\Helpers\Configuration;

beforeEach(function () {
    deleteTestConfigFile();
});

afterEach(function () {
    //
});

test('isConfigured method', function () {
    $config = new Configuration();
    $isConfigured = $config->isConfigured();
    expect($isConfigured)->toBeFalse();
    setTestConfigFile();
    $isConfigured = $config->isConfigured();
    expect($isConfigured)->toBeTrue();
});

test('get method', function () {
    setTestConfigFile();
    $config = new Configuration();
    expect($config->get('api_token'))->toEqual('myapikey123');
});

test('setCredentials method', function () {
    $config = new Configuration();

    // first time
    $config->saveConfig('mynewapitoken', 'json');
    expect($config->get('api_token'))->toEqual('mynewapitoken');

    // multiple teams
    $config->saveConfig('myteamapitoken', 'json', "team1");
    expect($config->get('api_token', 'team1'))->toEqual('myteamapitoken');

    // overwrite existing team apitoken
    $config->saveConfig('mynewteamapitoken', 'json', "team1");
    expect($config->get('api_token', "team1"))->toEqual('mynewteamapitoken');
});
