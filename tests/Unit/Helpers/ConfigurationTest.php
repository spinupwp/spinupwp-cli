<?php

use App\Helpers\Configuration;

beforeEach(function () {
    deleteTestConfigFile();
});

test('isConfigured method', function () {
    $config = resolve(Configuration::class);
    $isConfigured = $config->isConfigured();
    expect($isConfigured)->toBeFalse();
    setTestConfigFile();
    $isConfigured = $config->isConfigured();
    expect($isConfigured)->toBeTrue();
});

test('get method', function () {
    setTestConfigFile();
    $config = resolve(Configuration::class);
    expect($config->get('api_token'))->toEqual('myapikey123');
});

test('set method', function () {
    $config = resolve(Configuration::class);

    // first time
    $config->set('api_token', 'mynewapitoken');
    $config->set('format', 'json');
    expect($config->get('api_token'))->toEqual('mynewapitoken');

    // multiple teams
    $config->set('api_token', 'myteamapitoken', 'team1');
    expect($config->get('api_token', 'team1'))->toEqual('myteamapitoken');

    // overwrite existing team apitoken
    $config->set('api_token', 'mynewteamapitoken', 'team1');
    expect($config->get('api_token', 'team1'))->toEqual('mynewteamapitoken');
});
