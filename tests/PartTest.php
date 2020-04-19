<?php declare(strict_types=1);

namespace Multipart\Tests;

use PHPUnit\Framework\TestCase;
use Multipart\Part;

final class PartTest extends TestCase
{
    public function testContructParseThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Part('');
    }

    public function testGetBody(): void
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertEquals('foo', (string) (new Part($message))->getBody());
    }

    public function testHasHeader(): void
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertTrue((new Part($message))->hasHeader('content-disposition'));
    }

    public function testGetHeader(): void
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertEquals(['text/plain'], (new Part($message))->getHeader('content-type'));
        $this->assertEquals([], (new Part($message))->getHeader('foo'));
    }
}
