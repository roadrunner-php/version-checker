<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Composer;

use Composer\InstalledVersions;
use RoadRunner\VersionChecker\Composer\Package;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Data\DataSet;
use Testo\Lifecycle\AfterTest;
use Testo\Test;

#[Test]
final class PackageTest
{
    private const FIXTURES = __DIR__ . '/../../../fixtures/packages';

    public static function isSupportedVersionDataProvider(): \Traversable
    {
        yield ['1.0', true];
        yield ['1.0.0', true];
        yield ['^1.0', true];
        yield ['>=1.0', true];
        yield ['>1.0', true];
        yield ['1.0.*', true];
        yield ['^1.0 | ^2.0', true];
        yield ['^1.0 || ^2.0', true];
        yield ['1.0 - 2.0', true];
        yield ['dev-master', false];
        yield ['dev-feature/some', false];
        yield ['<2.0', true];
        yield ['<=2.0', true];
        yield ['*', true];
        yield ['latest', false];
    }

    public static function getMinVersionDataProvider(): \Traversable
    {
        yield ['1.0', '1.0.0.0'];
        yield ['1.0.0', '1.0.0.0'];
        yield ['^1.0', '1.0.0.0-dev'];
        yield ['>=1.0', '1.0.0.0-dev'];
        yield ['>1.0', '1.0.0.0'];
        yield ['1.0.*', '1.0.0.0-dev'];
        yield ['1.0.1', '1.0.1.0'];
        yield ['1.1.*', '1.1.0.0-dev'];
        yield ['1.1.1', '1.1.1.0'];
        yield ['^1.0 | ^2.0', '1.0.0.0-dev'];
        yield ['^1.0 || ^2.0', '1.0.0.0-dev'];
        yield ['1.0 - 2.0', '1.0.0.0-dev'];
        yield ['<2.0', '0.0.0.0-dev'];
        yield ['<=2.0', '0.0.0.0-dev'];
        yield ['*', '0.0.0.0-dev'];
        yield ['^2023.1 || ^2024.1', '2023.1.0.0-dev'];
    }

    public function testGetRequiredVersionsReturnsLowerBoundsOfSupportedConstraints(): void
    {
        InstalledVersions::reload([
            'root' => self::installedPackage('fixture/root', self::FIXTURES . '/no-require') + ['dev' => true],
            'versions' => [
                'fixture/stable' => self::installedPackage('fixture/stable', self::FIXTURES . '/stable'),
                'fixture/calendar' => self::installedPackage('fixture/calendar', self::FIXTURES . '/calendar'),
                'fixture/wildcard' => self::installedPackage('fixture/wildcard', self::FIXTURES . '/wildcard'),
                'fixture/branch' => self::installedPackage('fixture/branch', self::FIXTURES . '/branch'),
                'fixture/invalid' => self::installedPackage('fixture/invalid', self::FIXTURES . '/invalid'),
                'fixture/no-require' => self::installedPackage('fixture/no-require', self::FIXTURES . '/no-require'),
                'fixture/no-composer-json' => self::installedPackage('fixture/no-composer-json', self::FIXTURES . '/no-composer-json'),
                'fixture/metapackage' => ['type' => 'metapackage', 'install_path' => null] + self::installedPackage('fixture/metapackage', ''),
            ],
        ]);

        $versions = (new Package())->getRequiredVersions('fixture/roadrunner');

        Assert::array($versions)->sameElementsAs(['2.12.0.0-dev', '2023.1.0.0-dev', '0.0.0.0-dev']);
    }

    public function testGetRequiredVersionsReturnsEmptyListWhenNothingRequiresPackage(): void
    {
        $versions = (new Package())->getRequiredVersions('fixture/not-required-by-anything');

        Assert::same($versions, []);
    }

    #[DataProvider('isSupportedVersionDataProvider')]
    public function testIsSupportedVersion(string $version, bool $expected): void
    {
        $package = new Package();
        $ref = new \ReflectionMethod($package, 'isSupportedVersion');
        $ref->setAccessible(true);

        Assert::same($ref->invoke($package, $version), $expected);
    }

    #[DataProvider('getMinVersionDataProvider')]
    public function testGetMinVersion(string $version, string $expected): void
    {
        $package = new Package();
        $ref = new \ReflectionMethod($package, 'getMinVersion');
        $ref->setAccessible(true);

        Assert::same($ref->invoke($package, $version), $expected);
    }

    #[DataSet(['^2025.1 || ^3.0', '2025.1.0.0-dev'], 'calendar or 3.x')]
    #[DataSet(['^3.0 || ^2025.1', '2025.1.0.0-dev'], '3.x or calendar')]
    #[DataSet(['^3.0 || ^2024.1 || ^2.12', '2.12.0.0-dev'], 'all release lines')]
    #[DataSet(['>=2025.1 <2026.0 || >=3.0', '2025.1.0.0-dev'], 'explicit ranges')]
    public function testGetMinVersionFollowsReleaseOrderAcrossReleaseLines(string $version, string $expected): void
    {
        $package = new Package();
        $ref = new \ReflectionMethod($package, 'getMinVersion');
        $ref->setAccessible(true);

        Assert::same($ref->invoke($package, $version), $expected);
    }

    #[AfterTest]
    protected function restoreInstalledVersions(): void
    {
        // Null drops the override, so the next lookup reads vendor/composer/installed.php again.
        InstalledVersions::reload(null);
    }

    private static function installedPackage(string $name, string $installPath): array
    {
        return [
            'name' => $name,
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'library',
            'install_path' => $installPath,
            'aliases' => [],
            'dev_requirement' => false,
        ];
    }
}
