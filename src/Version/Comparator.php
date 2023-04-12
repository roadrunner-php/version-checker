<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use Composer\Semver\Comparator as SemverComparator;

final class Comparator implements ComparatorInterface
{
    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    public function greaterThan(string $requested, string $installed): bool
    {
        return SemverComparator::greaterThanOrEqualTo($installed, $requested);
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    public function lessThan(string $requested, string $installed): bool
    {
        return SemverComparator::lessThanOrEqualTo($installed, $requested);
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    public function equal(string $requested, string $installed): bool
    {
        return SemverComparator::equalTo($installed, $requested);
    }
}
