<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Composer\PackageInterface;
use RoadRunner\VersionChecker\Version\Required;

final class RequiredTest extends TestCase
{
    protected function tearDown(): void
    {
        // clean the cache
        $ref = new \ReflectionProperty(Required::class, 'cachedVersion');
        $ref->setAccessible(true);
        $ref->setValue(null);
    }

    /**
     * @dataProvider versionsDataProvider
     */
    public function testGetMinimumVersion(string $version, ?string $previous, string $expected): void
    {
        $required = new Required();
        $ref = new \ReflectionMethod($required, 'getMinimumVersion');
        $ref->setAccessible(true);

        $this->assertSame($expected, $ref->invoke($required, $version, $previous));
    }

    public function testGetRequiredVersion(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $package
            ->expects($this->once())
            ->method('getRequiredVersions')
            ->with('spiral/roadrunner')
            ->willReturn(['2.0', '1.0', '2.0.0.0-dev', '2.0.0-alpha']);

        $required = new Required($package);

        $this->assertSame('1.0', $required->getRequiredVersion());
    }

    public function testGetCachedVersion(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $package
            // $this->once() is important for this test!
            ->expects($this->once())
            ->method('getRequiredVersions')
            ->with('spiral/roadrunner')
            ->willReturn(['1.0']);

        $required = new Required($package);

        $this->assertSame('1.0', $required->getRequiredVersion());
        $this->assertSame('1.0', $required->getRequiredVersion());
    }

    public static function versionsDataProvider(): \Traversable
    {
        // Test case with $previous === null
        yield ['1.0.0', null, '1.0.0'];

        // Test case with $version < $previous
        yield ['1.0.0', '2.0.0', '1.0.0'];
        yield ['1.0.0-alpha', '1.0.0', '1.0.0-alpha'];

        // Test case with $version >= $previous
        yield ['2.0.0', '1.0.0', '1.0.0'];
        yield ['1.0.0', '1.0.0-alpha', '1.0.0-alpha'];
        yield ['1.0.0', '1.0.0', '1.0.0'];

        yield ['1.0.0.0', '1.1.0', '1.0.0.0'];
        yield ['1.0.0.0-dev', '1.0.0.0', '1.0.0.0-dev'];
    }
}
