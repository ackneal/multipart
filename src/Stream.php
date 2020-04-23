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
