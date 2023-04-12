<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Version;

use RoadRunner\VersionChecker\Environment\EnvironmentInterface;
use RoadRunner\VersionChecker\Environment\Native;
use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;
use RoadRunner\VersionChecker\Process\Process;
use RoadRunner\VersionChecker\Process\ProcessInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Installed implements InstalledInterface
{
    private const ENV_VARIABLE = 'RR_VERSION';

    /**
     * @var non-empty-string|null
     */
    private static ?string $cachedVersion = null;

    /**
     * @param non-empty-string $executablePath
     */
    public function __construct(
        private readonly ProcessInterface $process = new Process(),
        private readonly EnvironmentInterface $environment = new Native(),
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
        if (!empty(self::$cachedVersion)) {
            return self::$cachedVersion;
        }

        if (!empty(self::$cachedVersion = $this->getVersionFromEnv())) {
            return self::$cachedVersion;
        }

        if (!empty(self::$cachedVersion = $this->getVersionFromConsoleCommand())) {
            return self::$cachedVersion;
        }

        throw new RoadrunnerNotInstalledException('Unable to determine RoadRunner version.');
    }

    /**
     * @return non-empty-string|null
     */
    private function getVersionFromEnv(): ?string
    {
        /** @var string|null $version */
        $version = $this->environment->get(self::ENV_VARIABLE);

        if (\is_string($version) && !empty($version)) {
            return $version;
        }

        return null;
    }

    /**
     * @return non-empty-string|null
     * @throws RoadrunnerNotInstalledException
     */
    private function getVersionFromConsoleCommand(): ?string
    {
        try {
            $output = $this->process->exec([$this->executablePath, '--version']);
        } catch (ProcessFailedException) {
            throw new RoadrunnerNotInstalledException('Roadrunner is not installed.');
        }

        \preg_match('/\bversion (\d+\.\d+\.\d+[\w.-]*)/', $output, $matches);

        if (isset($matches[1]) && !empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }
}
