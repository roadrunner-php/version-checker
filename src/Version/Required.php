<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use Composer\Semver\Comparator as SemverComparator;
use Composer\Semver\VersionParser;
use RoadRunner\VersionChecker\Composer\Package;
use RoadRunner\VersionChecker\Composer\PackageInterface;

final class Required implements RequiredInterface
{
    private const ROADRUNNER_PACKAGE = 'spiral/roadrunner';

    public function __construct(
        private readonly PackageInterface $package = new Package()
    ) {
    }

    /**
     * @return non-empty-string|null
     */
    public function getRequiredVersion(): ?string
    {
        $parser = new VersionParser();

        $version = null;
        foreach ($this->package->getRequiredVersions(self::ROADRUNNER_PACKAGE) as $packageVersion) {
            /** @var non-empty-string $packageVersion */
            $packageVersion = $parser->normalize($packageVersion);
            $version = $this->getMinimumVersion($packageVersion, $version);
        }

        return $version;
    }

    /**
     * @param non-empty-string $version
     * @param non-empty-string|null $previous
     * @return non-empty-string
     */
    private function getMinimumVersion(string $version, ?string $previous = null): string
    {
        if ($previous === null) {
            return $version;
        }

        return SemverComparator::lessThan($version, $previous) ? $version : $previous;
    }
}
