<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Composer;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Composer\Package;

final class PackageTest extends TestCase
{
    /**
     * @dataProvider isSupportedVersionDataProvider
     */
    public function testIsSupportedVersion(string $version, bool $expected): void
    {
        $package = new Package();
        $ref = new \ReflectionMethod($package, 'isSupportedVersion');
        $ref->setAccessible(true);

        $this->assertSame($expected, $ref->invoke($package, $version));
    }

    /**
     * @dataProvider getMinVersionDataProvider
     */
    public function testGetMinVersion(string $version, string $expected): void
    {
        $package = new Package();
        $ref = new \ReflectionMethod($package, 'getMinVersion');
        $ref->setAccessible(true);

        $this->assertSame($expected, $ref->invoke($package, $version));
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
