<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;
use RoadRunner\VersionChecker\Process\ProcessInterface;
use RoadRunner\VersionChecker\Version\Installed;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class InstalledTest extends TestCase
{
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
