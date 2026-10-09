<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Composer;

use RoadRunner\VersionChecker\Composer\Package;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class PackageTest
{
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
    }
}
