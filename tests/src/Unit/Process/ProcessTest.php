<?php

declare(strict_types=1);

namespace RoadRunner\VersionChecker\Tests\Unit\Process;

use RoadRunner\VersionChecker\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class ProcessTest
{
    public function testExecReturnsOutput(): void
    {
        $process = new Process();

        $output = $process->exec([\PHP_BINARY, '-r', 'echo "rr version 2024.3.0";']);

        Assert::same($output, 'rr version 2024.3.0');
    }

    public function testExecThrowsOnNonZeroExitCode(): void
    {
        $process = new Process();

        Expect::exception(ProcessFailedException::class);
        $process->exec([\PHP_BINARY, '-r', 'exit(1);']);
    }
}
