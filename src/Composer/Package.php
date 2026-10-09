<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Composer;

use Composer\InstalledVersions;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\Constraint\MultiConstraint;
use Composer\Semver\VersionParser;
use RoadRunner\VersionChecker\Version\Comparator;

final class Package implements PackageInterface
{
    /**
     * @param non-empty-string $packageName
     * @return non-empty-string[]
     */
    #[\Override]
    public function getRequiredVersions(string $packageName): array
    {
        $versions = [];
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            $path = InstalledVersions::getInstallPath($package);
            if ($path !== null && \file_exists($path . '/composer.json')) {
                $content = \file_get_contents($path . '/composer.json');
                if ($content === false) {
                    continue;
                }

                /** @var array{require?: array<non-empty-string, non-empty-string>} $composerJson */
                $composerJson = \json_decode($content, true);

                if (
                    isset($composerJson['require'][$packageName]) &&
                    $this->isSupportedVersion($composerJson['require'][$packageName])
                ) {
                    $versions[] = $this->getMinVersion($composerJson['require'][$packageName]);
                }
            }
        }

        return $versions;
    }

    /**
     * @param non-empty-string $version
     */
    private function isSupportedVersion(string $version): bool
    {
        $parser = new VersionParser();

        try {
            $parser->parseConstraints($version);
        } catch (\Throwable) {
            return false;
        }

        return !\str_starts_with($version, 'dev-');
    }

    /**
     * @param non-empty-string $version
     *
     * @return non-empty-string
     */
    private function getMinVersion(string $version): string
    {
        $parser = new VersionParser();

        /** @var non-empty-string $min */
        $min = $this->getLowerBound($parser->parseConstraints($version));

        return $min;
    }

    /**
     * Composer orders `3.0` below `2023.1`, so the lowest alternative of an `||` constraint
     * is picked in RoadRunner release order instead.
     */
    private function getLowerBound(ConstraintInterface $constraint): string
    {
        if (!$constraint instanceof MultiConstraint || !$constraint->isDisjunctive()) {
            return $constraint->getLowerBound()->getVersion();
        }

        $min = null;
        foreach ($constraint->getConstraints() as $alternative) {
            $bound = $this->getLowerBound($alternative);
            if ($min === null || Comparator::compare($bound, $min) < 0) {
                $min = $bound;
            }
        }

        return $min ?? $constraint->getLowerBound()->getVersion();
    }
}
