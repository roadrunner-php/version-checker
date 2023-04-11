<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

interface RequiredInterface
{
    /**
     * @return non-empty-string
     */
    public function getRequiredVersion(): string;
}
