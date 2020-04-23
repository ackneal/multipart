<?php

declare(strict_types=1);

namespace Multipart;

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\StreamDecoratorTrait;

class Stream implements StreamInterface
{
    use StreamDecoratorTrait;

    private $stream;
    private $nl;
    private $boundary;
    private $hwm = 4096;
    private $buf;

    public function __construct($resource, string $boundary)
    {
        if (empty($boundary)) {
            throw new \InvalidArgumentException('boundary is empty');
        }
        $this->stream = Psr7\stream_for($resource);
        $this->boundary = [
            'dashPrefix' => '--' . $boundary,
            'dashEnclose' => '--' . $boundary . '--',
        ];
        $this->buf = new Psr7\BufferStream($this->hwm);
    }

    public function readPart()
    {
        while (($line = $this->readLine()) !== '') {
            if ($this->isDelimiter($line)) {
                return $this->resolvePart();
            }
            if ($this->isFinalBoundary($line)) {
                break;
            }
        }
    }

    private function resolvePart()
    {
        if ($this->scanUntilBoundary() === 0) {
            return null;
        }
        $message = substr($this->buf->getContents(), 0, -1 * strlen($this->nl));
        return new Part($message);
    }

    private function scanUntilBoundary()
    {
        $offset = $this->stream->tell();
        while (($line = $this->readLine()) !== '') {
            if ($this->isDelimiter($line) or $this->isFinalBoundary($line)) {
                $this->stream->seek($offset + $this->buf->getSize());
                return $this->buf->getSize();
            }
            $this->buf->write($line);
        }
        $this->buf->close();
        return 0;
    }

    private function readLine()
    {
        return Psr7\readLine($this->stream, $this->hwm);
    }

    private function isDelimiter(string $line): bool
    {
        if (strpos($line, $this->boundary['dashPrefix']) !== 0) {
            return false;
        }
        $rest = $this->ltrimLWSP(substr($line, strlen($this->boundary['dashPrefix'])));
        if (is_null($this->nl) and preg_match("/^\r?\n$/", $rest) === 1) {
            $this->nl = $rest;
        }
        return $rest === $this->nl;
    }

    private function isFinalBoundary(string $line): bool
    {
        if (strpos($line, $this->boundary['dashPrefix']) !== 0) {
            return false;
        }
        $rest = $this->ltrimLWSP(substr($line, strlen($this->boundary['dashEnclose'])));
        return strlen($rest) === 0 or $rest === $this->nl;
    }

    private function ltrimLWSP(string $line): string
    {
        return ltrim($line, " \t");
    }
}
