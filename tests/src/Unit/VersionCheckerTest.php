<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Exception\RequiredVersionException;
use RoadRunner\VersionChecker\Exception\UnsupportedVersionException;
use RoadRunner\VersionChecker\Version\ComparatorInterface;
use RoadRunner\VersionChecker\Version\InstalledInterface;
use RoadRunner\VersionChecker\Version\RequiredInterface;
use RoadRunner\VersionChecker\VersionChecker;

final class VersionCheckerTest extends TestCase
{
    /**
     * @dataProvider invalidVersionsDataProvider
     */
    public function testSuccessGreaterThanWithoutVersion(?string $version = null): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('greaterThan')
            ->willReturn(true);

        $requiredVersion = $this->createMock(RequiredInterface::class);
        $requiredVersion
            ->expects($this->once())
            ->method('getRequiredVersion')
            ->willReturn('1.0');

        $checker = new VersionChecker(
            $this->createMock(InstalledInterface::class),
            $requiredVersion,
            $comparator
        );

        $checker->greaterThan($version);
    }

    public function testSuccessGreaterThanWithVersion(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('greaterThan')
            ->willReturn(true);

        $requiredVersion = $this->createMock(RequiredInterface::class);
        $requiredVersion
            ->expects($this->never())
            ->method('getRequiredVersion');

        $checker = new VersionChecker(
            $this->createMock(InstalledInterface::class),
            $requiredVersion,
            $comparator
        );

        $checker->greaterThan('1.0');
    }

    public function testGreaterThanWithoutVersionAndWithoutRoadRunnerPackage(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->never())
            ->method('greaterThan');

        $requiredVersion = $this->createMock(RequiredInterface::class);
        $requiredVersion
            ->expects($this->once())
            ->method('getRequiredVersion')
            ->willReturn(null);

        $checker = new VersionChecker(
            $this->createMock(InstalledInterface::class),
            $requiredVersion,
            $comparator
        );

        $this->expectException(RequiredVersionException::class);
        $checker->greaterThan();
    }

    /**
     * @dataProvider invalidVersionsDataProvider
     */
    public function testFailGreaterThanWithoutVersion(?string $version = null): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('greaterThan')
            ->willReturn(false);

        $installedVersion = $this->createMock(InstalledInterface::class);
        $installedVersion
            ->expects($this->once())
            ->method('getInstalledVersion')
            ->willReturn('1.0');

        $requiredVersion = $this->createMock(RequiredInterface::class);
        $requiredVersion
            ->method('getRequiredVersion')
            ->willReturn('2023.1');

        $checker = new VersionChecker($installedVersion, $requiredVersion, $comparator);

        try {
            $checker->greaterThan($version);
        } catch (UnsupportedVersionException $exception) {
        }

        $this->assertSame('1.0', $exception->getInstalledVersion());
        $this->assertSame('2023.1', $exception->getRequestedVersion());
        $this->assertSame(
            'Installed RoadRunner version `1.0` not supported. Requires version `2023.1` or higher.',
            $exception->getMessage()
        );
    }

    public function testFailGreaterThanWithVersion(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('greaterThan')
            ->willReturn(false);

        $installedVersion = $this->createMock(InstalledInterface::class);
        $installedVersion
            ->expects($this->once())
            ->method('getInstalledVersion')
            ->willReturn('1.0');

        $requiredVersion = $this->createMock(RequiredInterface::class);
        $requiredVersion
            ->expects($this->never())
            ->method('getRequiredVersion');

        $checker = new VersionChecker($installedVersion, $requiredVersion, $comparator);

        try {
            $checker->greaterThan('2.0');
        } catch (UnsupportedVersionException $exception) {
        }

        $this->assertSame('1.0', $exception->getInstalledVersion());
        $this->assertSame('2.0', $exception->getRequestedVersion());
        $this->assertSame(
            'Installed RoadRunner version `1.0` not supported. Requires version `2.0` or higher.',
            $exception->getMessage()
        );
    }

    public function testSuccessLessThan(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('lessThan')
            ->willReturn(true);

        $checker = new VersionChecker(
            $this->createMock(InstalledInterface::class),
            $this->createMock(RequiredInterface::class),
            $comparator
        );

        $checker->lessThan('2.0');
    }

    public function testFailLessThan(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('lessThan')
            ->willReturn(false);

        $installedVersion = $this->createMock(InstalledInterface::class);
        $installedVersion
            ->expects($this->once())
            ->method('getInstalledVersion')
            ->willReturn('2.0');

        $checker = new VersionChecker(
            $installedVersion,
            $this->createMock(RequiredInterface::class),
            $comparator
        );

        try {
            $checker->lessThan('1.0');
        } catch (UnsupportedVersionException $exception) {
        }

        $this->assertSame('2.0', $exception->getInstalledVersion());
        $this->assertSame('1.0', $exception->getRequestedVersion());
        $this->assertSame(
            'Installed RoadRunner version `2.0` not supported. Requires version `1.0` or lower.',
            $exception->getMessage()
        );
    }

    public function testSuccessEqual(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('equal')
            ->willReturn(true);

        $checker = new VersionChecker(
            $this->createMock(InstalledInterface::class),
            $this->createMock(RequiredInterface::class),
            $comparator
        );

        $checker->equal('2.0');
    }

    public function testFailEqual(): void
    {
        $comparator = $this->createMock(ComparatorInterface::class);
        $comparator
            ->expects($this->once())
            ->method('equal')
            ->willReturn(false);

        $installedVersion = $this->createMock(InstalledInterface::class);
        $installedVersion
            ->expects($this->once())
            ->method('getInstalledVersion')
            ->willReturn('2.0');

        $checker = new VersionChecker(
            $installedVersion,
            $this->createMock(RequiredInterface::class),
            $comparator
        );

        try {
            $checker->equal('1.0');
        } catch (UnsupportedVersionException $exception) {
        }

        $this->assertSame('2.0', $exception->getInstalledVersion());
        $this->assertSame('1.0', $exception->getRequestedVersion());
        $this->assertSame(
            'Installed RoadRunner version `2.0` not supported. Requires version `1.0`.',
            $exception->getMessage()
        );
    }

    /**
     * @dataProvider getFormattedMessageDataProvider
     */
    public function testGetFormattedMessage(string $version, string $expected): void
    {
        $checker = new VersionChecker();
        $ref = new \ReflectionMethod($checker, 'getFormattedMessage');
        $ref->setAccessible(true);

        $this->assertSame(
            \sprintf('installed 1 required %s', $expected),
            $ref->invoke($checker, 'installed %s required %s', '1', $version)
        );
    }

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
}
