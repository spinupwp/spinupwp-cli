<?php

use App\Helpers\Configuration;

beforeEach(function () {
    deleteTestConfigFile();
});

afterEach(function () {
    //
});

test('isConfigured method', function () {

    $isConfigured = Configuration::isConfigured();
    expect($isConfigured)->toBeFalse();
    setTestConfigFile();
    $isConfigured = Configuration::isConfigured();
    expect($isConfigured)->toBeTrue();
});

test('get method', function () {
    setTestConfigFile();
    expect(Configuration::get('api_token'))->toEqual('myapikey123');
});

test('setCredentials method', function () {
    // first time
    Configuration::saveConfig('mynewapitoken', 'json');
    expect(Configuration::get('api_token'))->toEqual('mynewapitoken');
    // multiple teams
    Configuration::saveConfig('myteamapitoken', 'json', "team1");
    expect(Configuration::get('api_token', 'team1'))->toEqual('myteamapitoken');
    //overwrite existing team apitoken
    Configuration::saveConfig('mynewteamapitoken', 'json', "team1");
    expect(Configuration::get('api_token', "team1"))->toEqual('mynewteamapitoken');
});
