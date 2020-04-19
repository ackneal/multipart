<?php

declare(strict_types=1);

namespace Multipart;

use Psr\Http\Message\MessageInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\MessageTrait;
use GuzzleHttp\Psr7\Rfc7230;

class Part implements MessageInterface
{
    use MessageTrait;

    public function __construct($message)
    {
        $this->parse($message);
    }

    private function parse($message): void
    {
        $section = preg_split("/((?<=\r\n)|(?<=\n))\r?\n/", $message, 2);
        if ($section === false || count($section) !== 2) {
            throw new \InvalidArgumentException('Missing header delimiter');
        }
        list($header, $body) = $section;
        $this->stream = Psr7\stream_for($body);
        preg_match_all(Rfc7230::HEADER_REGEX, $header, $matchs, PREG_SET_ORDER);
        $headers = [];
        foreach ($matchs as $match) {
            $headers[$match[1]][] = $match[2];
        }
        $this->setHeaders($headers);
    }
}
