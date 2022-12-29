# Contributing

## Development

After cloning the repository locally, run:

```
composer install
```

To run the locally checked-out version of the CLI tool, use `php spinupwp` from within the repository's root.

If you would like to use the CLI tool against a local version of the SpinupWP API. Open your `~/.spinupwp/config.json` file and add `api_url` to a profile.

```json
{
    "default": {
        "api_url": "http:\/\/api.spinupwp.test\/v1\/",
        "api_token": "eyJ...",
        "format": "table"
    }
}
```

## Releasing a New Version

Update the `version` in `config/app.php` to reflect the new release. Ensure you follow [Semantic Versioning](https://semver.org/).

Then, build a new version of the CLI tool:

```
php spinupwp app:build spinupwp
```

Commit the changes to the main branch.

On GitHub, [create a new release](https://github.com/spinupwp/spinupwp-cli/releases/new).  Set the tag and release title to the semantic version, but prepend the letter **v**. For example, if releasing version `1.1.1`, the tag and release title should be `v1.1.1`. Leave target set to main and add any release notes to the description field.

Hit 'Publish release' to finalize the release. This will automatically update [Packagist](https://packagist.org/packages/spinupwp/spinupwp-cli) with the latest version.
