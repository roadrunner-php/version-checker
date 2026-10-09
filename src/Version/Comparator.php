<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use Composer\Semver\Comparator as SemverComparator;
use Composer\Semver\VersionParser;

final class Comparator implements ComparatorInterface
{
    /**
     * Calendar versions (2023.x - 2025.x) were released after 2.x and before 3.x,
     * so they are ordered by release line first and by semver within a line.
     */
    private const CALENDAR_MAJOR_MIN = 2023;

    private VersionParser $parser;

    public function __construct(?VersionParser $parser = null)
    {
        $this->parser = $parser ?? new VersionParser();
    }

    /**
     * Compares two RoadRunner versions in release order.
     *
     * @return int<-1, 1> Negative when $a is older than $b, positive when newer, zero when equal.
     *
     * @throws \UnexpectedValueException When a version cannot be parsed.
     */
    public static function compare(string $a, string $b): int
    {
        $parser = new VersionParser();
        $a = $parser->normalize($a);
        $b = $parser->normalize($b);

        $lineA = self::releaseLine($a);
        $lineB = self::releaseLine($b);

        if ($lineA !== null && $lineB !== null && $lineA !== $lineB) {
            return $lineA <=> $lineB;
        }

        if (SemverComparator::equalTo($a, $b)) {
            return 0;
        }

        return SemverComparator::greaterThan($a, $b) ? 1 : -1;
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    #[\Override]
    public function greaterThan(string $requested, string $installed): bool
    {
        return self::compare($this->parser->normalize($installed), $this->parser->normalize($requested)) >= 0;
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    #[\Override]
    public function lessThan(string $requested, string $installed): bool
    {
        return self::compare($this->parser->normalize($installed), $this->parser->normalize($requested)) <= 0;
    }

    /**
     * @param non-empty-string $requested
     * @param non-empty-string $installed
     */
    #[\Override]
    public function equal(string $requested, string $installed): bool
    {
        return self::compare($this->parser->normalize($installed), $this->parser->normalize($requested)) === 0;
    }

    /**
     * @return int<0, 2>|null Null for non-numeric versions such as `dev-master`.
     */
    private static function releaseLine(string $version): ?int
    {
        if (!\preg_match('/^v?(\d+)\./', $version, $matches)) {
            return null;
        }

        $major = (int) $matches[1];

        return match (true) {
            $major <= 2 => 0,
            $major >= self::CALENDAR_MAJOR_MIN => 1,
            default => 2,
        };
    }
}
