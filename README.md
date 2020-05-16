# Multipart, PHP multipart parsing

[![Build Status](https://travis-ci.org/ackneal/multipart.svg?branch=master)](https://travis-ci.org/github/ackneal/multipart)

Multipart is PHP MIME multipart parsing that makes it easy to get a single part  in a multipart body.

```php
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

$stream = new \Multipart\Stream($message, 'simple boundary');
while ($part = $stream->readPart()) {
    echo $part->getHeaderLine('content-type') . PHP_EOL;
    echo $part->getBody() . PHP_EOL;
}

/**
 *
 * This is implicitly typed plain ASCII text.
 * It does NOT end with a linebreak.
 * text/plain; charset=us-ascii
 * This is explicitly typed plain ASCII text.
 * It DOES end with a linebreak.
 */
```

## Examples

Sufficient for HTTP multipart/form-data ([RFC 2388](https://tools.ietf.org/html/rfc2388))
```php
$message = <<<EOT
--simple boundary
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foobar
--simple boundary--
EOT;

$stream = new \Multipart\Stream($message, 'simple boundary');
$part = $stream->readPart();
echo $part->getFormName() . PHP_EOL; // foo
echo $part->getFileName() . PHP_EOL; // bar.txt
echo $part->getBody() . PHP_EOL; // foobar
```

Handle HTTP multipart/* request
```php
preg_match('/^multipart\/.+; boundary=(?<boundary>.+)$/', $_SERVER['CONTENT_TYPE'], $m);
$stream = new \Multipart\Stream(fopen('php://input', 'r'), $m['boundary']);
```
