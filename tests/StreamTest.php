<?php

declare(strict_types=1);

namespace Multipart\Tests;

use PHPUnit\Framework\TestCase;
use Multipart\Stream;

final class StreamTest extends TestCase
{
    public function testContructWithInvalidThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Stream('foo', '');
    }
}
