<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use Mockery;
use RoadRunner\VersionChecker\Environment\EnvironmentInterface;
use RoadRunner\VersionChecker\Exception\RoadrunnerNotInstalledException;
use RoadRunner\VersionChecker\Process\ProcessInterface;
use RoadRunner\VersionChecker\Version\Installed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Expect;
use Testo\Lifecycle\AfterTest;
use Testo\Test;

final class InstalledTest
{
    #[AfterTest]
    protected function tearDown(): void
    {
        // clean the cache
        $ref = new \ReflectionProperty(Installed::class, 'cachedVersion');
        $ref->setAccessible(true);
        $ref->setValue(null);
    }

    #[Test]
    #[DataProvider('outputDataProvider')]
    public function testGetInstalledVersion(string $version, string $output): void
    {
        $process = Mockery::mock(ProcessInterface::class)->shouldIgnoreMissing();
        $process->shouldReceive('exec')->once()->with(['./rr', '--version'], Mockery::andAnyOtherArgs())->andReturn($output);

        $installed = new Installed($process);

        Assert::same($installed->getInstalledVersion(), $version);
    }

    #[Test]
    public function testCachedVersion(): void
    {
        $env = Mockery::mock(EnvironmentInterface::class)->shouldIgnoreMissing();
        $env->shouldReceive('get')->once()->with('RR_VERSION', Mockery::andAnyOtherArgs())->andReturn('2023.1.0');

        $installed = new Installed(environment: $env);

        $version = $installed->getInstalledVersion();
        $version2 = $installed->getInstalledVersion();

        Assert::same($version, '2023.1.0');
        Assert::same($version2, '2023.1.0');
    }

    public function getVersionFromEnv(): void
    {
        $env = Mockery::mock(EnvironmentInterface::class)->shouldIgnoreMissing();
        $env->shouldReceive('get')->once()->with('RR_VERSION', Mockery::andAnyOtherArgs())->andReturn('2023.1.0');

        $process = Mockery::mock(ProcessInterface::class)->shouldIgnoreMissing();
        $process->shouldNotReceive('exec');

        $installed = new Installed($process, $env);

        Assert::same($installed->getInstalledVersion(), '2023.1.0');
    }

    public function getVersionFromConsoleCommand(): void
    {
        $env = Mockery::mock(EnvironmentInterface::class)->shouldIgnoreMissing();
        $env->shouldReceive('get')->once()->with('RR_VERSION', Mockery::andAnyOtherArgs())->andReturn(null);

        $process = Mockery::mock(ProcessInterface::class)->shouldIgnoreMissing();
        $process->shouldReceive('exec')->once()->with(['./rr', '--version'], Mockery::andAnyOtherArgs())->andReturn('version 2023.1.0');

        $installed = new Installed($process, $env);

        Assert::same($installed->getInstalledVersion(), '2023.1.0');
    }

    #[Test]
    public function testGetInstalledVersionRoadRunnerIsNotInstalled(): void
    {
        $process = Mockery::mock(ProcessInterface::class)->shouldIgnoreMissing();
        $process->shouldReceive('exec')->once()->with(['./rr', '--version'], Mockery::andAnyOtherArgs())->andThrow((new \ReflectionClass(ProcessFailedException::class))->newInstanceWithoutConstructor());

        $installed = new Installed($process);

        Expect::exception(RoadrunnerNotInstalledException::class);
        $installed->getInstalledVersion();
    }

    #[Test]
    public function testGetInstalledVersionUnableToDetermineVersion(): void
    {
        $process = Mockery::mock(ProcessInterface::class)->shouldIgnoreMissing();
        $process->shouldReceive('exec')->once()->with(['./rr', '--version'], Mockery::andAnyOtherArgs())->andReturn('foo');

        $installed = new Installed($process);

        Expect::exception(RoadrunnerNotInstalledException::class)->withMessageContaining('Unable to determine RoadRunner version.');
        $installed->getInstalledVersion();
    }

    public static function outputDataProvider(): \Traversable
    {
        yield ['2.12.3', 'rr version 2.12.3 (build time: 2023-02-16T13:08:23+0000, go1.20), OS: darwin, arch: arm64'];
        yield ['2023.1.0-rc.2', 'rr version 2023.1.0-rc.2 (build time: 2023-02-16T13:08:23+0000, go1.20)'];
        yield ['2023.1.0-beta', 'version 2023.1.0-beta'];
    }
}
