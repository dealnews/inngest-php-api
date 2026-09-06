<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * The content of a file read from a sandbox. The API returns this as a
 * raw byte stream (not a JSON envelope), so this is a plain holder for
 * the response body and its Content-Type header rather than a
 * fromArray()-based model.
 */
class SandboxFileContent {

    public function __construct(
        public readonly string $content,
        public readonly ?string $content_type = null,
    ) {
    }
}
