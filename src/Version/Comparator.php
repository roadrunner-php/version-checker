<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

final class Comparator implements ComparatorInterface
{
    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    public function greaterThan(string $requested, string $installed): bool
    {
        // TODO need implementation

        return true;
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    public function lessThan(string $requested, string $installed): bool
    {
        // TODO need implementation

        return true;
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    public function equal(string $requested, string $installed): bool
    {
        // TODO need implementation

        return true;
    }
}
