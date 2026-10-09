<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use RoadRunner\VersionChecker\Version\Comparator;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Test;

#[Test]
final class ComparatorTest
{
    #[DataProvider('greaterThanDataProvider')]
    public function testGreaterThan(string $requested, string $installed, bool $expected): void
    {
        $comparator = new Comparator();

        Assert::same($comparator->greaterThan($requested, $installed), $expected);
    }

    #[DataProvider('lessThanDataProvider')]
    public function testLessThan(string $requested, string $installed, bool $expected): void
    {
        $comparator = new Comparator();

        Assert::same($comparator->lessThan($requested, $installed), $expected);
    }

    #[DataProvider('equalDataProvider')]
    public function testEqual(string $requested, string $installed, bool $expected): void
    {
        $comparator = new Comparator();

        Assert::same($comparator->equal($requested, $installed), $expected);
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
        // '0' is satisfied by any installed release
        yield ['0', '2.12.3', true];
        // a release candidate is older than its release
        yield ['2023.1.0', '2023.1.0-rc.2', false];
        yield ['2023.1.0-rc.2', '2023.1.0', true];
        // the `v` prefix is ignored
        yield ['v2023.1.0', '2023.1.0', true];
        yield ['v3.0.0', '2025.1.5', false];
        // a dev branch is older than any release
        yield ['dev-master', '2.12.3', true];
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
        yield ['v3.0.0', '2025.1.5', true];
        yield ['2023.1.0', '2023.1.0-rc.2', true];
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
        yield ['v2023.1.0', '2023.1.0', true];
        yield ['3.0', '3.0.0', true];
        yield ['2023.1.0-rc.2', '2023.1.0-RC2', true];
    }

    #[DataProvider('compareDataProvider')]
    public function testCompare(string $a, string $b, int $expected): void
    {
        Assert::same(Comparator::compare($a, $b), $expected);
    }

    #[DataSet(['v3.0.1', '3.0.0', 1], 'v prefix, newer patch')]
    #[DataSet(['v2023.1.0', '2023.1.0', 0], 'v prefix, same version')]
    #[DataSet(['3.0', '3.0.0', 0], 'short form of the same version')]
    public function testCompareAcceptsUnnormalizedVersions(string $a, string $b, int $expected): void
    {
        Assert::same(Comparator::compare($a, $b), $expected);
    }

    #[DataSet(['*'], 'wildcard')]
    #[DataSet(['latest'], 'word')]
    public function testRejectsNonVersionStrings(string $requested): void
    {
        $comparator = new Comparator();

        Expect::exception(\UnexpectedValueException::class);
        $comparator->greaterThan($requested, '3.0.0');
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
        yield ['3.0.0.0', 'dev-master', 1];
    }
}
