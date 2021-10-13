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

test('getCredentials method', function () {
    setTestConfigFile();
    expect(Configuration::getCredentials())->toEqual('myapikey123');
});

test('setCredentials method', function () {
    // first time
    Configuration::saveCredentials('mynewapitoken', 'json');
    expect(Configuration::getCredentials())->toEqual('mynewapitoken');
    // multiple teams
    Configuration::saveCredentials('myteamapitoken', 'json', "team1");
    expect(Configuration::getCredentials("team1"))->toEqual('myteamapitoken');
    //overwrite existing team apitoken
    Configuration::saveCredentials('mynewteamapitoken', 'json', "team1");
    expect(Configuration::getCredentials("team1"))->toEqual('mynewteamapitoken');
});
