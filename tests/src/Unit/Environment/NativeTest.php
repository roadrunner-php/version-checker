<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Environment;

use RoadRunner\VersionChecker\Environment\Native;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class NativeTest
{
    #[DataProvider('valuesDataProvider')]
    public function testGet(mixed $value, mixed $expected, string $key): void
    {
        $native = new Native([
            '1' => '2.12.3',
            '2' => 1,
            '3' => true,
        ]);

        Assert::same($native->get($key), $expected);
    }


    public static function valuesDataProvider(): \Traversable
    {
        yield ['2.12.3', '2.12.3', '1'];
        yield [1, 1, '2'];
        yield [true, true, '3'];
    }
}
