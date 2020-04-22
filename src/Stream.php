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
}
