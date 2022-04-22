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
In addition, you should make sure the `/vendor/bin` directory in your global Composer home directory is in your system's "PATH". Depending on your operating system this could be either `~/.composer/` or `~/.config/composer/`. You can use the `composer config --global home` command to check this location.

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

### Servers
```bash
# Delete a server
spinupwp servers:delete <server_id>

# Get a server
spinupwp servers:get <server_id> --fields=id,name,ip_address,ubuntu_version,database.server

# List all servers
spinupwp servers:list --fields=id,name,ip_address,ubuntu_version,database.server

# Reboot a server
spinupwp servers:reboot <server_id>

# Reboot all servers
spinupwp servers:reboot --all

# Start an SSH session
spinupwp servers:ssh <server_id> <user>
```
You can pass any properties of the [Server Schema](https://api.spinupwp.com/?shell#tocS_Server) to the `--fields` flag.
Nested properties should use dot notation, for example, `database.server`.

### Services
```bash
# Restart MySQL on a server
spinupwp services:mysql <server_id>

# Restart MySQL on all servers
spinupwp services:mysql --all

# Restart Nginx on a server
spinupwp services:nginx <server_id>

# Restart Nginx on all servers
spinupwp services:nginx --all

# Restart PHP on a server
spinupwp services:php <server_id>

# Restart PHP on all servers
spinupwp services:php --all
```

### Sites
```bash
# Create a site
spinupwp sites:create <server_id>

# Delete a site
spinupwp sites:delete <site_id>

# Run a Git deployment
spinupwp sites:deploy <site_id>

# Get a site
spinupwp sites:get <site_id> --fields=id,server_id,domain,site_user,php_version,page_cache,https

# List all sites
spinupwp sites:list --fields=id,server_id,domain,site_user,php_version,page_cache,https

# Purge the page cache for a site
spinupwp sites:purge <site_id> --cache=page

# Purge the page cache for all sites
spinupwp sites:purge --all --cache=page

# Purge the object cache for a site
spinupwp sites:purge <site_id> --cache=object

# Purge the object cache for all sites
spinupwp sites:purge --all --cache=object

# Start an SSH session as the site user
spinupwp sites:ssh <site_id>
```
You can pass any properties of the [Site Schema](https://api.spinupwp.com/?shell#tocS_Site) to the `--fields` flag.
Nested properties should use dot notation, for example, `backups.next_run_time` or `git.branch`.

```bash
# Create a site using field flags instead of interactive prompts
spinupwp sites:create <server_id> --installation-method="<installation_method>" \
  --domain="<domain>" --https-enabled --site-user="<site_user>" --db-name="<database_name>" \
  --db-user="<database_user>" --db-pass="<database_password>" --wp-title="<wordpress_site_title>" \
  --wp-admin-email="<wordpress_admin_user_email>" --wp-admin-user="<wordpress_admin_user_username>" \
  --wp-admin-pass="<wordpress_admin_user_password>" --php-version="<php_version>" --page-cache-enabled
```

A full reference of the available flags is available anytime.

```bash
# Display available Arguments and Options for site:create 
spinupwp sites:create --help
```

## Upgrade
To update the SpinupWP CLI to the latest version, run:
```bash
composer global update
```
