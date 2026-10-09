<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Process;

interface ProcessInterface
{
    /**
     * @param array<string> $command
     */
    public function exec(array $command): string;
}
