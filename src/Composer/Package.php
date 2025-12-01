<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Composer;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

final class Package implements PackageInterface
{
    /**
     * @param non-empty-string $packageName
     *
     * @return string[]
     *
     * @psalm-return list<non-empty-string>
     */
    #[\Override]
    public function getRequiredVersions(string $packageName): array
    {
        $versions = [];
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            $path = InstalledVersions::getInstallPath($package);
            if ($path !== null && \file_exists($path . '/composer.json')) {
                /** @var array{require?: array<non-empty-string, non-empty-string>} $composerJson */
                $fileContent = \file_get_contents($path . '/composer.json');
                /** @var array<string, mixed>|null $composerJson */
                $composerJson = $fileContent === false ? null : \json_decode($fileContent, true);

                if (isset($composerJson['require'][$packageName])) {
                    /** @var mixed $rawPackage */
                    $rawPackage = $composerJson['require'][$packageName];

                    if (is_string($rawPackage) && strlen($rawPackage) > 0) {
                        assert($rawPackage !== '');

                        if ($this->isSupportedVersion($rawPackage)) {
                            $versions[] = $this->getMinVersion($rawPackage);
                        }
                    }
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

        $constraint = $parser->parseConstraints($version);

        /** @var non-empty-string $min */
        $min = $constraint->getLowerBound()->getVersion();

        return $min;
    }
}
