<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Environment\EnvironmentInterface;
use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;
use RoadRunner\VersionChecker\Process\ProcessInterface;
use RoadRunner\VersionChecker\Version\Installed;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class InstalledTest extends TestCase
{
    protected function tearDown(): void
    {
        // clean the cache
        $ref = new \ReflectionProperty(Installed::class, 'cachedVersion');
        $ref->setAccessible(true);
        $ref->setValue(null);
    }

    /**
     * @dataProvider outputDataProvider
     */
    public function testGetInstalledVersion(string $version, string $output): void
    {
        $process = $this->createMock(ProcessInterface::class);
        $process
            ->expects($this->once())
            ->method('exec')
            ->with(['./rr', '--version'])
            ->willReturn($output);

        $installed = new Installed($process);

        $this->assertSame($version, $installed->getInstalledVersion());
    }

    public function testCachedVersion(): void
    {
        $env = $this->createMock(EnvironmentInterface::class);
        $env
            // $this->once() is important for this test!
            ->expects($this->once())
            ->method('get')
            ->with('RR_VERSION')
            ->willReturn('2023.1.0');

        $installed = new Installed(environment: $env);

        $version = $installed->getInstalledVersion();
        $version2 = $installed->getInstalledVersion();

        $this->assertSame('2023.1.0', $version);
        $this->assertSame('2023.1.0', $version2);
    }

    public function getVersionFromEnv(): void
    {
        $env = $this->createMock(EnvironmentInterface::class);
        $env
            ->expects($this->once())
            ->method('get')
            ->with('RR_VERSION')
            ->willReturn('2023.1.0');

        $process = $this->createMock(ProcessInterface::class);
        $process->expects($this->never());

        $installed = new Installed($process, $env);

        $this->assertSame('2023.1.0', $installed->getInstalledVersion());
    }

    public function getVersionFromConsoleCommand(): void
    {
        $env = $this->createMock(EnvironmentInterface::class);
        $env
            ->expects($this->once())
            ->method('get')
            ->with('RR_VERSION')
            ->willReturn(null);

        $process = $this->createMock(ProcessInterface::class);
        $process
            ->expects($this->once())
            ->method('exec')
            ->with(['./rr', '--version'])
            ->willReturn('version 2023.1.0');

        $installed = new Installed($process, $env);

        $this->assertSame('2023.1.0', $installed->getInstalledVersion());
    }

    public function testGetInstalledVersionRoadRunnerIsNotInstalled(): void
    {
        $process = $this->createMock(ProcessInterface::class);
        $process
            ->expects($this->once())
            ->method('exec')
            ->with(['./rr', '--version'])
            ->willThrowException(
                (new \ReflectionClass(ProcessFailedException::class))->newInstanceWithoutConstructor()
            );

        $installed = new Installed($process);

        $this->expectException(RoadrunnerNotInstalledException::class);
        $installed->getInstalledVersion();
    }

    public function testGetInstalledVersionUnableToDetermineVersion(): void
    {
        $process = $this->createMock(ProcessInterface::class);
        $process
            ->expects($this->once())
            ->method('exec')
            ->with(['./rr', '--version'])
            ->willReturn('foo');

        $installed = new Installed($process);

        $this->expectException(RoadrunnerNotInstalledException::class);
        $this->expectExceptionMessage('Unable to determine RoadRunner version.');
        $installed->getInstalledVersion();
    }

    public static function outputDataProvider(): \Traversable
    {
        yield ['2.12.3', 'rr version 2.12.3 (build time: 2023-02-16T13:08:23+0000, go1.20), OS: darwin, arch: arm64'];
        yield ['2023.1.0-rc.2', 'rr version 2023.1.0-rc.2 (build time: 2023-02-16T13:08:23+0000, go1.20)'];
        yield ['2023.1.0-beta', 'version 2023.1.0-beta'];
    }
}
