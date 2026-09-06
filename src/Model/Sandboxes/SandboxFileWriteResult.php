<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * The result of writing a file to a sandbox.
 */
class SandboxFileWriteResult {

    public function __construct(
        public readonly ?string $path = null,
        public readonly ?int $bytes_written = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            path:          $data['path'] ?? null,
            bytes_written: isset($data['bytesWritten']) ? (int) $data['bytesWritten'] : null,
        );
    }
}
