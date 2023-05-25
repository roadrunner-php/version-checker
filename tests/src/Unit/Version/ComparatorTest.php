<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Version\Comparator;

final class ComparatorTest extends TestCase
{
    /**
     * @dataProvider greaterThanDataProvider
     */
    public function testGreaterThan(string $requested, string $installed, bool $expected): void
    {
        $comparator = new Comparator();

        $this->assertSame($expected, $comparator->greaterThan($requested, $installed));
    }

    /**
     * @dataProvider lessThanDataProvider
     */
    public function testLessThan(string $requested, string $installed, bool $expected): void
    {
        $comparator = new Comparator();

        $this->assertSame($expected, $comparator->lessThan($requested, $installed));
    }

    /**
     * @dataProvider equalDataProvider
     */
    public function testEqual(string $requested, string $installed, bool $expected): void
    {
        $comparator = new Comparator();

        $this->assertSame($expected, $comparator->equal($requested, $installed));
    }

    public static function greaterThanDataProvider(): \Traversable
    {
        // requested version equal to installed version
        yield ['1.0.0', '1.0.0', true];
        // requested version less than installed version
        yield ['1.0.0', '2.0.0', true];
        // requested version greater than installed version
        yield ['2.0.0', '1.0.0', false];
        // requested version with same major and minor version as installed version, but greater patch version
        yield ['1.0.1', '1.0.0', false];
        // requested version with same major version as installed version, but greater minor version
        yield ['1.1.0', '1.0.0', false];
        // requested version with pre-release identifier
        yield ['2.0.0-alpha', '2.0.0', true];
        yield ['2.0.0-alpha', '2.0.0-beta', true];
        yield ['2.0.0-alpha', '2.0.0-alpha.1', true];
        yield ['2.0.0-alpha', '2.0.0-alpha', true];
        yield ['2023.1.0.0-dev', '2023.1.0', true];
    }

    public static function lessThanDataProvider(): \Traversable
    {
        // requested version equal to installed version
        yield ['1.0.0', '1.0.0', true];
        // requested version less than installed version
        yield ['1.0.0', '2.0.0', false];
        // requested version greater than installed version
        yield ['2.0.0', '1.0.0', true];
        // requested version with same major and minor version as installed version, but greater patch version
        yield ['1.0.1', '1.0.0', true];
        // requested version with same major version as installed version, but greater minor version
        yield ['1.1.0', '1.0.0', true];
        // requested version with pre-release identifier
        yield ['2.0.0-alpha', '2.0.0', false];
        yield ['2.0.0-alpha', '2.0.0-beta', false];
        yield ['2.0.0-alpha', '2.0.0-alpha.1', false];
        yield ['2023.1.0.0-dev', '2023.1.0', false];
        yield ['2023.1.0', '2023.1.0.0-dev', true];
    }

    public static function equalDataProvider(): \Traversable
    {
        // requested version equal to installed version
        yield ['1.0.0', '1.0.0', true];
        // requested version less than installed version
        yield ['1.0.0', '2.0.0', false];
        // requested version greater than installed version
        yield ['2.0.0', '1.0.0', false];
        // requested version with same major and minor version as installed version, but greater patch version
        yield ['1.0.1', '1.0.0', false];
        // requested version with same major version as installed version, but greater minor version
        yield ['1.1.0', '1.0.0', false];
        // requested version with pre-release identifier
        yield ['2.0.0-alpha', '2.0.0', false];
        yield ['2.0.0-alpha', '2.0.0-beta', false];
        yield ['2.0.0-alpha', '2.0.0-alpha.1', false];
        yield ['2.0.0-alpha', '2.0.0-alpha', true];
        yield ['2.0.0-alpha.1', '2.0.0-alpha.1', true];
    }
}
