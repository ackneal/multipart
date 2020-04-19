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

    private $disposition;
    private $disposition_params;

    public function __construct($message)
    {
        $this->parse($message);
    }

    public function getFormName(): string
    {
        $this->parseContentDisposition();
        if ($this->disposition !== 'form-data') {
            return '';
        }
        return $this->disposition_params['name'];
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

    private function parseContentDisposition(): void
    {
        if (! is_null($this->disposition)) {
            return;
        }
        $expect_params = ['name', 'filename'];
        $this->disposition = '';
        $this->disposition_params = array_fill_keys($expect_params, '');
        $values = $this->getHeader('Content-Disposition');
        if (count($values) === 0) {
            return;
        }
        $header = current(Psr7\parse_header($values[0]));
        if ($header !== false and count($header) !== 0) {
            $this->disposition = array_shift($header);
            $this->disposition_params = $header + $this->disposition_params;
        }
    }
}
