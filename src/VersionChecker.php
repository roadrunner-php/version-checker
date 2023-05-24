<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker;

use RoadRunner\VersionChecker\Exception\RequiredVersionException;
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
    private InstalledInterface $installedVersion;
    private RequiredInterface $requiredVersion;
    private ComparatorInterface $comparator;

    public function __construct($installedVersion = null, $requiredVersion = null, $comparator = null)
    {
        $this->installedVersion = $installedVersion ?? new Installed();
        $this->requiredVersion = $requiredVersion ?? new Required();
        $this->comparator = $comparator ?? new Comparator();
    }

    /**
     * @param non-empty-string|null $version
     *
     * @throws UnsupportedVersionException
     * @throws RoadrunnerNotInstalledException
     * @throws RequiredVersionException
     */
    public function greaterThan(?string $version = null): void
    {
        if (empty($version)) {
            $version = $this->requiredVersion->getRequiredVersion();
        }

        if (empty($version)) {
            throw new RequiredVersionException(
                'Unable to determine required RoadRunner version.' .
                ' Please specify the required version in the `$version` parameter.'
            );
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
