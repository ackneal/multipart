<?php

namespace Multipart\Tests;

use PHPUnit\Framework\TestCase;
use Multipart\Part;

final class PartTest extends TestCase
{
    public function testContructParseThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Part('');
    }

    public function testParseMessageWithoutHeaders()
    {
        $message = <<<EOT

foo
EOT;
        $part = new Part($message);
        $this->assertEmpty($part->getHeaders());
        $this->assertEquals('foo', (string) $part->getBody());
    }

    public function testGetBody()
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertEquals('foo', (string) (new Part($message))->getBody());
    }

    public function testHasHeader()
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertTrue((new Part($message))->hasHeader('content-disposition'));
    }

    public function testGetHeader()
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

    public function testGetFormName()
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertEquals('foo', (new Part($message))->getFormName());
    }

    public function testGetFormNameWithoutDisposition()
    {
        $message = <<<EOT
Content-Type: application/json; charset=UTF-8

{
  "name": "myObject"
}
EOT;
        $this->assertEquals('', (new Part($message))->getFormName());
    }

    public function testGetFileName()
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
EOT;
        $this->assertEquals('bar.txt', (new Part($message))->getFileName());
    }

    public function testGetFileNameWithoutFilename()
    {
        $message = <<<EOT
Content-Disposition: form-data; name="foo";"

foo
EOT;
        $this->assertEquals('', (new Part($message))->getFileName());
    }
}
