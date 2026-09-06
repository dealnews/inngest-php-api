<?php

namespace DealNews\InngestApi\Model\Sandboxes;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A single chunk of log/output data from a sandbox or sandbox process.
 */
class SandboxLogChunk {

    /**
     * @param ?string $data Raw decoded bytes. The API sends this
     *        base64-encoded (`format: byte`); `fromArray()` decodes it so
     *        consumers receive the raw bytes/string directly.
     */
    public function __construct(
        public readonly ?\DateTimeImmutable $at = null,
        public readonly ?string $data = null,
        public readonly ?string $encoding = null,
        public readonly ?SandboxLogStream $stream = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            at:       DateTimeConverter::parse($data['at'] ?? null),
            data:     isset($data['data']) ? base64_decode($data['data']) : null,
            encoding: $data['encoding'] ?? null,
            stream:   isset($data['stream']) ? SandboxLogStream::tryFrom($data['stream']) : null,
        );
    }
}
