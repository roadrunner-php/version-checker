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
        // calendar versions are newer than 2.x and older than 3.x
        yield ['2023.1', '3.0.0', true];
        yield ['2025.1.5.0-dev', 'v3.0.0', true];
        yield ['3.0.0.0-dev', '2025.1.5', false];
        yield ['3.0', '2.12.3', false];
        yield ['2023.1', '2.12.3', false];
        yield ['2.12', '2024.3.0', true];
        yield ['3.1.0', '3.0.0', false];
        yield ['3.0.0', '3.1.0', true];
        yield ['3.0.0-beta.1', '3.0.0', true];
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
        // calendar versions are newer than 2.x and older than 3.x
        yield ['2025.1', '3.0.0', false];
        yield ['3.0', '2025.1.5', true];
        yield ['2023.1', '2.12.3', true];
        yield ['2.12', '2024.3.0', false];
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
        yield ['3.0.0', 'v3.0.0', true];
        yield ['2025.1.0', '3.0.0', false];
    }

    /**
     * @dataProvider compareDataProvider
     */
    public function testCompare(string $a, string $b, int $expected): void
    {
        $this->assertSame($expected, Comparator::compare($a, $b));
    }

    public static function compareDataProvider(): \Traversable
    {
        yield ['3.0.0.0', '3.0.0.0', 0];
        yield ['3.0.0.0', '2025.1.5.0', 1];
        yield ['2025.1.5.0', '3.0.0.0', -1];
        yield ['2023.1.0.0-dev', '2.12.3.0', 1];
        yield ['2.12.3.0', '3.0.0.0-dev', -1];
        yield ['2025.1.0.0', '2024.3.0.0', 1];
        yield ['3.0.1.0', '3.0.0.0', 1];
        yield ['dev-master', '3.0.0.0', -1];
    }
}
