<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;
use RoadRunner\VersionChecker\Process\Process;
use RoadRunner\VersionChecker\Process\ProcessInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Installed implements InstalledInterface
{
    /**
     * @param non-empty-string $executablePath
     */
    public function __construct(
        private readonly ProcessInterface $process = new Process(),
        private readonly string $executablePath = './rr'
    ) {
    }

    /**
     * @return non-empty-string
     *
     * @throws RoadrunnerNotInstalledException
     */
    public function getInstalledVersion(): string
    {
        try {
            $output = $this->process->exec([$this->executablePath, '--version']);
        } catch (ProcessFailedException) {
            throw new RoadrunnerNotInstalledException('Roadrunner is not installed.');
        }

        \preg_match('/\bversion (\d+\.\d+\.\d+[\w.-]*)/', $output, $matches);

        if (!empty($matches[1])) {
            return $matches[1];
        }

        throw new RoadrunnerNotInstalledException('Unable to determine RoadRunner version.');
    }
}
