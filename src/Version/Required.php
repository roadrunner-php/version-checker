<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use Composer\InstalledVersions;
use Composer\Semver\Comparator as SemverComparator;
use Composer\Semver\VersionParser;

final class Required implements RequiredInterface
{
    private const ROADRUNNER_PACKAGE = 'spiral/roadrunner';

    /**
     * @return non-empty-string|null
     */
    public function getRequiredVersion(): ?string
    {
        $parser = new VersionParser();

        $version = null;
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            $path = InstalledVersions::getInstallPath($package);
            if ($path !== null && \file_exists($path . '/composer.json')) {
                /** @var array $composerJson */
                $composerJson = \json_decode(\file_get_contents($path . '/composer.json'), true);

                if (isset($composerJson['require']) && \is_array($composerJson['require'])) {
                    /** @var non-empty-string $packageVersion */
                    foreach ($composerJson['require'] as $package => $packageVersion) {
                        if ($package === self::ROADRUNNER_PACKAGE) {
                            /** @var non-empty-string $packageVersion */
                            $packageVersion = $parser->normalize($packageVersion);
                            $version = $this->getMinimumVersion($packageVersion, $version);
                        }
                    }
                }
            }
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
