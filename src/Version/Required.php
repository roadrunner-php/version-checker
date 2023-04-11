<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

final class Required implements RequiredInterface
{
    /**
     * @return non-empty-string
     */
    public function getRequiredVersion(): string
    {
        return '1.0'; // TODO need implementation
    }
}
