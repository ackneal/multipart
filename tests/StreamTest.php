<?php

namespace Multipart\Tests;

use PHPUnit\Framework\TestCase;
use Multipart\Stream;

final class StreamTest extends TestCase
{
    public function testContructWithInvalidThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Stream('foo', '');
    }

    public function testSimpleMessage()
    {
        $message = <<<EOT
This is the preamble.  It is to be ignored, though it
is a handy place for mail composers to include an
explanatory note to non-MIME compliant readers.
--simple boundary

This is implicitly typed plain ASCII text.
It does NOT end with a linebreak.
--simple boundary
Content-type: text/plain; charset=us-ascii

This is explicitly typed plain ASCII text.
It DOES end with a linebreak.

--simple boundary--
This is the epilogue.  It is also to be ignored.
EOT;
        $stream = new Stream($message, 'simple boundary');
        while ($p = $stream->readPart()) {
            $parts[] = $p;
        }
        $this->assertCount(2, $parts);
        $this->assertEmpty($parts[0]->getHeaders());
        $this->assertEquals("This is implicitly typed plain ASCII text.\nIt does NOT end with a linebreak.", $parts[0]->getBody()->getContents());
        $this->assertCount(1, $parts[1]->getHeaders());
        $this->assertEquals('text/plain; charset=us-ascii', $parts[1]->getHeaderLine('content-type'));
        $this->assertEquals("This is explicitly typed plain ASCII text.\nIt DOES end with a linebreak.\n", $parts[1]->getBody()->getContents());
    }
}
