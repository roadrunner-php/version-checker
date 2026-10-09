<p align="center">
    <a href="https://roadrunner.dev"><picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
        <img alt="RoadRunner" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8" style="width: 6in; display: block">
    </picture></a>
</p>

<p align="center">Check the installed RoadRunner version from PHP</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://docs.roadrunner.dev)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/roadrunner-server)

[![Psalm Level](https://shepherd.dev/github/roadrunner-php/version-checker/level.svg)](https://shepherd.dev/github/roadrunner-php/version-checker)
[![Type Coverage](https://shepherd.dev/github/roadrunner-php/version-checker/coverage.svg)](https://shepherd.dev/github/roadrunner-php/version-checker)
[![Mutation testing badge](https://img.shields.io/endpoint?url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Froadrunner-php%2Fversion-checker%2F1.x)](https://dashboard.stryker-mutator.io/reports/github.com/roadrunner-php/version-checker/1.x)

</div>

<br />

The package finds out which RoadRunner version is installed and checks it against a version constraint, so an application or a library can fail early with a clear message instead of running against an incompatible server. RoadRunner 2.x, 2023.x–2025.x and 3.x are supported.

## Get Started

### Installation

```bash
composer require roadrunner-php/version-checker
```

[![PHP](https://img.shields.io/packagist/php-v/roadrunner-php/version-checker.svg?style=flat-square&logo=php)](https://packagist.org/packages/roadrunner-php/version-checker)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/roadrunner-php/version-checker.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/roadrunner-php/version-checker)
[![License](https://img.shields.io/packagist/l/roadrunner-php/version-checker.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/roadrunner-php/version-checker.svg?style=flat-square)](https://packagist.org/packages/roadrunner-php/version-checker/stats)

### Usage

Use the `RoadRunner\VersionChecker\VersionChecker` methods to check the compatibility of the installed RoadRunner
version. The VersionChecker class has three public methods:

- **greaterThan** - Checks if the installed version of RoadRunner is **greater than or equal** to the specified version.
  If no version is specified, the minimum required version will be determined based on the minimum required version of
  the `spiral/roadrunner` package.
- **lessThan** - Checks if the installed version of RoadRunner is **less than or equal** to the specified version.
- **equal** - Checks if the installed version of RoadRunner is **equal** to the specified version.

Versions are compared in RoadRunner release order: `2.x` < `2023.x`, `2024.x`, `2025.x` < `3.x`.
For example, `3.0.0` satisfies `greaterThan('2025.1')`, and `2025.1.5` does not satisfy `greaterThan('3.0')`.

All three methods throw an `RoadRunner\VersionChecker\Exception\UnsupportedVersionException` if the installed version
of RoadRunner does not meet the specified requirements. If RoadRunner is not installed, a
`RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException` is thrown.

```php
use RoadRunner\VersionChecker\VersionChecker;
use RoadRunner\VersionChecker\Exception\UnsupportedVersionException;

$checker = new VersionChecker();

try {
    $checker->greaterThan('3.0');
} catch (UnsupportedVersionException $exception) {
    var_dump($exception->getMessage()); // Installed RoadRunner version `2025.1.5` not supported. Requires version `3.0` or higher.
    var_dump($exception->getInstalledVersion()); // 2025.1.5
    var_dump($exception->getRequestedVersion()); // 3.0
}

try {
    $checker->lessThan('2024.3');
} catch (UnsupportedVersionException $exception) {
    var_dump($exception->getMessage()); // Installed RoadRunner version `2025.1.5` not supported. Requires version `2024.3` or lower.
    var_dump($exception->getInstalledVersion()); // 2025.1.5
    var_dump($exception->getRequestedVersion()); // 2024.3
}

try {
    $checker->equal('3.0');
} catch (UnsupportedVersionException $exception) {
    var_dump($exception->getMessage()); // Installed RoadRunner version `2025.1.5` not supported. Requires version `3.0`.
    var_dump($exception->getInstalledVersion()); // 2025.1.5
    var_dump($exception->getRequestedVersion()); // 3.0
}
```

## How the installed version is detected

When the `RR_VERSION` environment variable is set, its value is used as the installed version. Otherwise the
checker runs `./rr --version` from the current working directory. The detected version is cached for the lifetime of
the process.

## Path to the RoadRunner binary

To configure the `VersionChecker` to search for the RoadRunner binary in a location other than the default
(`./rr`), you can bind the `RoadRunner\VersionChecker\Version\InstalledInterface`
within application container using the `RoadRunner\VersionChecker\Version\Installed` class and passing the desired
file path as the **$executablePath** parameter. After that, you can retrieve the VersionChecker class from
application container.

Example with Spiral Framework container:

```php
use RoadRunner\VersionChecker\Version\InstalledInterface;
use RoadRunner\VersionChecker\Version\Installed;
use RoadRunner\VersionChecker\VersionChecker;

$container->bindSingleton(InstalledInterface::class, new Installed(executablePath: 'some/path'));
$checker = $container->get(VersionChecker::class);
```

Without a container, pass the instance directly:

```php
$checker = new VersionChecker(new Installed(executablePath: 'some/path'));
```

## Testing

```bash
composer test
```

```bash
composer psalm
```

```bash
composer cs:diff
```
