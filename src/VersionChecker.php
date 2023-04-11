<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker;

use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;
use RoadRunner\VersionChecker\Exception\UnsupportedVersionException;
use RoadRunner\VersionChecker\Version\Comparator;
use RoadRunner\VersionChecker\Version\ComparatorInterface;
use RoadRunner\VersionChecker\Version\Installed;
use RoadRunner\VersionChecker\Version\InstalledInterface;
use RoadRunner\VersionChecker\Version\Required;
use RoadRunner\VersionChecker\Version\RequiredInterface;

final class VersionChecker
{
    public function __construct(
        private readonly InstalledInterface $installedVersion = new Installed(),
        private readonly RequiredInterface $requiredVersion = new Required(),
        private readonly ComparatorInterface $comparator = new Comparator()
    ) {
    }

    /**
     * @param non-empty-string|null $version
     *
     * @throws UnsupportedVersionException
     * @throws RoadrunnerNotInstalledException
     */
    public function greaterThan(?string $version = null): void
    {
        if (empty($version)) {
            $version = $this->requiredVersion->getRequiredVersion();
        }
        $installedVersion = $this->installedVersion->getInstalledVersion();

        if (!$this->comparator->greaterThan($version, $installedVersion)) {
            throw new UnsupportedVersionException(\sprintf(
                'Installed RoadRunner version `%s` not supported. Requires version `%s` or higher.',
                $installedVersion,
                $version
            ), $installedVersion, $version);
        }
    }

    /**
     * @param non-empty-string $version
     *
     * @throws UnsupportedVersionException
     * @throws RoadrunnerNotInstalledException
     */
    public function lessThan(string $version): void
    {
        $installedVersion = $this->installedVersion->getInstalledVersion();

        if (!$this->comparator->lessThan($version, $installedVersion)) {
            throw new UnsupportedVersionException(\sprintf(
                'Installed RoadRunner version `%s` not supported. Requires version `%s` or lower.',
                $installedVersion,
                $version
            ), $installedVersion, $version);
        }
    }

    /**
     * @param non-empty-string $version
     *
     * @throws UnsupportedVersionException
     * @throws RoadrunnerNotInstalledException
     */
    public function equal(string $version): void
    {
        $installedVersion = $this->installedVersion->getInstalledVersion();

        if (!$this->comparator->equal($version, $installedVersion)) {
            throw new UnsupportedVersionException(\sprintf(
                'Installed RoadRunner version `%s` not supported. Requires version `%s`.',
                $installedVersion,
                $version
            ), $installedVersion, $version);
        }
    }
}
