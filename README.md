# SpinupWP CLI

[![Tests](https://github.com/deliciousbrains/spinupwp-cli/actions/workflows/tests.yml/badge.svg?event=push)](https://github.com/deliciousbrains/spinupwp-cli/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/deliciousbrains/spinupwp-cli)](https://packagist.org/packages/deliciousbrains/spinupwp-cli)
[![Latest Stable Version](https://img.shields.io/packagist/v/deliciousbrains/spinupwp-cli)](https://packagist.org/packages/deliciousbrains/spinupwp-cli)
[![License](https://img.shields.io/packagist/l/deliciousbrains/spinupwp-cli)](https://packagist.org/packages/deliciousbrains/spinupwp-cli)

## Installation
To get started, require the package globally via [Composer](https://getcomposer.org):
```bash
composer global require deliciousbrains/spinupwp-cli
```
In addition, you should make sure the `~/.composer/vendor/bin` directory is in your system's "PATH".

## Usage
Installing the SpinupWP CLI provides access to the `spinupwp` command.
```bash
spinupwp <command>
````
You will need to generate an API token to interact with the SpinupWP CLI. After you have generated an API token, you should configure your default profile:
```bash
spinupwp configure
````
You can configure multiple profiles, which is useful if you're a member of multiple teams:
```bash
spinupwp configure --profile=hellfishmedia
```
To run a command using a specific profile, pass the profile option:
```bash
spinupwp servers:list --profile=hellfishmedia
```
If no profile is supplied, your default profile will be used (if configured).

## Upgrade
To update the SpinupWP CLI to the latest version, run:
```bash
composer global update
```
