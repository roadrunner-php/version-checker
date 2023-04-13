<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Composer;

use Composer\InstalledVersions;

final class Package implements PackageInterface
{
    /**
     * @param non-empty-string $packageName
     * @return non-empty-string[]
     */
    public function getRequiredVersions(string $packageName): array
    {
        $versions = [];
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            $path = InstalledVersions::getInstallPath($package);
            if ($path !== null && \file_exists($path . '/composer.json')) {
                /** @var array{require?: array<non-empty-string, non-empty-string>} $composerJson */
                $composerJson = \json_decode(\file_get_contents($path . '/composer.json'), true);

                if (isset($composerJson['require'][$packageName])) {
                    $versions[] = $composerJson['require'][$packageName];
                }
            }
        }

        return $versions;
    }
}
