<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Version;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Version\Required;

final class RequiredTest extends TestCase
{
    /**
     * @dataProvider versionsDataProvider
     */
    public function testGetMinimumVersion(string $version, ?string $previous, string $expected): void
    {
        $required = new Required();
        $ref = new \ReflectionMethod($required, 'getMinimumVersion');

        $this->assertSame($expected, $ref->invoke($required, $version, $previous));
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
    }
}
