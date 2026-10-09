<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit;

use RoadRunner\VersionChecker\Exception\RequiredVersionException;
use RoadRunner\VersionChecker\Exception\UnsupportedVersionException;
use RoadRunner\VersionChecker\Version\Comparator;
use RoadRunner\VersionChecker\Version\ComparatorInterface;
use RoadRunner\VersionChecker\Version\InstalledInterface;
use RoadRunner\VersionChecker\Version\RequiredInterface;
use RoadRunner\VersionChecker\VersionChecker;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Expect;
use Testo\Test;

#[Test]
final class VersionCheckerTest
{
    public static function invalidVersionsDataProvider(): \Traversable
    {
        yield [''];
        yield [null];
    }

    public static function getFormattedMessageDataProvider(): \Traversable
    {
        yield ['1', '1'];
        yield ['1.1', '1.1'];
        yield ['1.2.3', '1.2.3'];
        yield ['1.2.3.4', '1.2.3'];
        yield ['v1.2.3.4', '1.2.3'];
        yield ['2023.1.0.0-dev', '2023.1.0'];
    }

    #[DataProvider('invalidVersionsDataProvider')]
    public function testSuccessGreaterThanWithoutVersion(?string $version = null): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('greaterThan')->once()->andReturn(true);

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->once()->andReturn('1.0');

        $checker = new VersionChecker(
            \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing(),
            $requiredVersion,
            $comparator,
        );

        $checker->greaterThan($version);
    }

    public function testSuccessGreaterThanWithVersion(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('greaterThan')->once()->andReturn(true);

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->never();

        $checker = new VersionChecker(
            \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing(),
            $requiredVersion,
            $comparator,
        );

        $checker->greaterThan('1.0');
    }

    public function testGreaterThanWithoutVersionAndWithoutRoadRunnerPackage(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('greaterThan')->never();

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->once()->andReturn(null);

        $checker = new VersionChecker(
            \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing(),
            $requiredVersion,
            $comparator,
        );

        Expect::exception(RequiredVersionException::class);
        $checker->greaterThan();
    }

    #[DataProvider('invalidVersionsDataProvider')]
    public function testFailGreaterThanWithoutVersion(?string $version = null): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('greaterThan')->once()->andReturn(false);

        $installedVersion = \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing();
        $installedVersion->shouldReceive('getInstalledVersion')->once()->andReturn('1.0');

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->andReturn('2023.1');

        $checker = new VersionChecker($installedVersion, $requiredVersion, $comparator);

        try {
            $checker->greaterThan($version);
        } catch (UnsupportedVersionException $exception) {
        }

        Assert::same($exception->getInstalledVersion(), '1.0');
        Assert::same($exception->getRequestedVersion(), '2023.1');
        Assert::same($exception->getMessage(), 'Installed RoadRunner version `1.0` not supported. Requires version `2023.1` or higher.');
    }

    public function testFailGreaterThanWithVersion(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('greaterThan')->once()->andReturn(false);

        $installedVersion = \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing();
        $installedVersion->shouldReceive('getInstalledVersion')->once()->andReturn('1.0');

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->never();

        $checker = new VersionChecker($installedVersion, $requiredVersion, $comparator);

        try {
            $checker->greaterThan('2.0');
        } catch (UnsupportedVersionException $exception) {
        }

        Assert::same($exception->getInstalledVersion(), '1.0');
        Assert::same($exception->getRequestedVersion(), '2.0');
        Assert::same($exception->getMessage(), 'Installed RoadRunner version `1.0` not supported. Requires version `2.0` or higher.');
    }

    public function testGreaterThanTreatsZeroAsVersion(): void
    {
        $installedVersion = \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing();
        $installedVersion->shouldReceive('getInstalledVersion')->once()->andReturn('2.12.3');

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->never();

        $checker = new VersionChecker($installedVersion, $requiredVersion, new Comparator());

        $checker->greaterThan('0');
    }

    public function testFailGreaterThanReportsRequiredVersionWithoutStability(): void
    {
        $installedVersion = \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing();
        $installedVersion->shouldReceive('getInstalledVersion')->once()->andReturn('2.12.3');

        $requiredVersion = \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing();
        $requiredVersion->shouldReceive('getRequiredVersion')->once()->andReturn('2023.1.0.0-dev');

        $checker = new VersionChecker($installedVersion, $requiredVersion, new Comparator());

        Expect::exception(UnsupportedVersionException::class)
            ->withMessage('Installed RoadRunner version `2.12.3` not supported. Requires version `2023.1.0` or higher.');
        $checker->greaterThan();
    }

    public function testSuccessLessThan(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('lessThan')->once()->andReturn(true);

        $checker = new VersionChecker(
            \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing(),
            \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing(),
            $comparator,
        );

        $checker->lessThan('2.0');
    }

    public function testFailLessThan(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('lessThan')->once()->andReturn(false);

        $installedVersion = \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing();
        $installedVersion->shouldReceive('getInstalledVersion')->once()->andReturn('2.0');

        $checker = new VersionChecker(
            $installedVersion,
            \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing(),
            $comparator,
        );

        try {
            $checker->lessThan('1.0');
        } catch (UnsupportedVersionException $exception) {
        }

        Assert::same($exception->getInstalledVersion(), '2.0');
        Assert::same($exception->getRequestedVersion(), '1.0');
        Assert::same($exception->getMessage(), 'Installed RoadRunner version `2.0` not supported. Requires version `1.0` or lower.');
    }

    public function testSuccessEqual(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('equal')->once()->andReturn(true);

        $checker = new VersionChecker(
            \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing(),
            \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing(),
            $comparator,
        );

        $checker->equal('2.0');
    }

    public function testFailEqual(): void
    {
        $comparator = \Mockery::mock(ComparatorInterface::class)->shouldIgnoreMissing();
        $comparator->shouldReceive('equal')->once()->andReturn(false);

        $installedVersion = \Mockery::mock(InstalledInterface::class)->shouldIgnoreMissing();
        $installedVersion->shouldReceive('getInstalledVersion')->once()->andReturn('2.0');

        $checker = new VersionChecker(
            $installedVersion,
            \Mockery::mock(RequiredInterface::class)->shouldIgnoreMissing(),
            $comparator,
        );

        try {
            $checker->equal('1.0');
        } catch (UnsupportedVersionException $exception) {
        }

        Assert::same($exception->getInstalledVersion(), '2.0');
        Assert::same($exception->getRequestedVersion(), '1.0');
        Assert::same($exception->getMessage(), 'Installed RoadRunner version `2.0` not supported. Requires version `1.0`.');
    }

    #[DataProvider('getFormattedMessageDataProvider')]
    public function testGetFormattedMessage(string $version, string $expected): void
    {
        $checker = new VersionChecker();
        $ref = new \ReflectionMethod($checker, 'getFormattedMessage');
        $ref->setAccessible(true);

        Assert::same($ref->invoke($checker, 'installed %s required %s', '1', $version), \sprintf('installed 1 required %s', $expected));
    }
}
