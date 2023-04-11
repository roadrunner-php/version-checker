<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;

final class Installed implements InstalledInterface
{
    /**
     * @return non-empty-string
     *
     * @throws RoadrunnerNotInstalledException
     */
    public function getInstalledVersion(): string
    {
        // TODO need implementation

        throw new RoadrunnerNotInstalledException();
    }
}
