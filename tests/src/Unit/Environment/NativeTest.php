<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use RoadRunner\VersionChecker\Environment\Native;

final class NativeTest extends TestCase
{
    /**
     * @dataProvider valuesDataProvider
     */
    public function testGet(mixed $value, mixed $expected, string $key): void
    {
        $native = new Native([
            '1' => '2.12.3',
            '2' => 1,
            '3' => true,
        ]);

        $this->assertSame($expected, $native->get($key));
    }


    public static function valuesDataProvider(): \Traversable
    {
        yield ['2.12.3', '2.12.3', '1'];
        yield [1, 1, '2'];
        yield [true, true, '3'];
    }
}
