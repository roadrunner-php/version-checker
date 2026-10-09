<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use Mockery;
use RoadRunner\VersionChecker\Composer\PackageInterface;
use RoadRunner\VersionChecker\Version\Required;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\AfterTest;
use Testo\Test;

#[Test]
final class RequiredTest
{
    #[AfterTest]
    protected function tearDown(): void
    {
        // clean the cache
        $ref = new \ReflectionProperty(Required::class, 'cachedVersion');
        $ref->setAccessible(true);
        $ref->setValue(null);
    }

    #[DataProvider('versionsDataProvider')]
    public function testGetMaximumVersion(string $version, ?string $previous, string $expected): void
    {
        $required = new Required();
        $ref = new \ReflectionMethod($required, 'getMaximumVersion');
        $ref->setAccessible(true);

        Assert::same($ref->invoke($required, $version, $previous), $expected);
    }

    public function testGetRequiredVersion(): void
    {
        $package = Mockery::mock(PackageInterface::class)->shouldIgnoreMissing();
        $package->shouldReceive('getRequiredVersions')->once()->with('spiral/roadrunner', Mockery::andAnyOtherArgs())->andReturn(['2.0', '1.0', '2.0.0.0-dev', '2.0.0-alpha']);

        $required = new Required($package);

        Assert::same($required->getRequiredVersion(), '2.0.0.0-dev');
    }

    #[DataProvider('releaseLinesDataProvider')]
    public function testGetRequiredVersionPicksLatestReleaseLine(array $versions, string $expected): void
    {
        $package = Mockery::mock(PackageInterface::class)->shouldIgnoreMissing();
        $package->shouldReceive('getRequiredVersions')->once()->with('spiral/roadrunner', Mockery::andAnyOtherArgs())->andReturn($versions);

        $required = new Required($package);

        Assert::same($required->getRequiredVersion(), $expected);
    }

    public function testGetRequiredVersionWithoutRoadRunnerRequirement(): void
    {
        $package = Mockery::mock(PackageInterface::class)->shouldIgnoreMissing();
        $package->shouldReceive('getRequiredVersions')->once()->with('spiral/roadrunner', Mockery::andAnyOtherArgs())->andReturn([]);

        $required = new Required($package);

        Assert::null($required->getRequiredVersion());
    }

    public function testGetCachedVersion(): void
    {
        $package = Mockery::mock(PackageInterface::class)->shouldIgnoreMissing();
        $package->shouldReceive('getRequiredVersions')->once()->with('spiral/roadrunner', Mockery::andAnyOtherArgs())->andReturn(['1.0']);

        $required = new Required($package);

        Assert::same($required->getRequiredVersion(), '1.0');
        Assert::same($required->getRequiredVersion(), '1.0');
    }

    public static function releaseLinesDataProvider(): \Traversable
    {
        yield '2.x, calendar, 3.x' => [['2.12.0.0-dev', '2023.1.0.0-dev', '3.0.0.0-dev'], '3.0.0.0-dev'];
        yield '3.x, calendar, 2.x' => [['3.0.0.0-dev', '2025.1.0.0-dev', '2.12.0.0-dev'], '3.0.0.0-dev'];
        yield 'calendar first' => [['2025.1.0.0-dev', '3.0.0.0-dev', '2023.1.0.0-dev'], '3.0.0.0-dev'];
        yield 'calendar only' => [['2024.1.0.0-dev', '2023.1.0.0-dev', '2025.1.0.0-dev'], '2025.1.0.0-dev'];
        yield 'wildcard and 2.x' => [['0.0.0.0-dev', '2.12.0.0-dev'], '2.12.0.0-dev'];
        yield 'pre-release and its release' => [['3.0.0.0-beta1', '3.0.0.0'], '3.0.0.0'];
    }

    public static function versionsDataProvider(): \Traversable
    {
        // Test case with $previous === null
        yield ['1.0.0', null, '1.0.0'];

        // Test case with $version < $previous
        yield ['1.0.0', '2.0.0', '2.0.0'];
        yield ['1.0.0-alpha', '1.0.0', '1.0.0'];

        // Test case with $version >= $previous
        yield ['2.0.0', '1.0.0', '2.0.0'];
        yield ['1.0.0', '1.0.0-alpha', '1.0.0'];
        yield ['1.0.0', '1.0.0', '1.0.0'];

        yield ['1.0.0.0', '1.1.0', '1.1.0'];
        yield ['1.0.0.0-dev', '1.0.0.0', '1.0.0.0'];

        // calendar versions are older than 3.x
        yield ['3.0.0.0-dev', '2025.1.5.0-dev', '3.0.0.0-dev'];
        yield ['2023.1.0.0-dev', '3.0.0.0-dev', '3.0.0.0-dev'];
        yield ['2023.1.0.0-dev', '2.12.0.0-dev', '2023.1.0.0-dev'];
    }
}
