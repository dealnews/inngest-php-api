<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * Buffered output for a sandbox process, as returned by the non-streaming
 * process output endpoint.
 */
class SandboxProcessOutput {

    /**
     * @param SandboxLogChunk[] $chunks
     */
    public function __construct(
        public readonly array $chunks = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            chunks: array_map(static fn (array $chunk) => SandboxLogChunk::fromArray($chunk), $data['chunks'] ?? []),
        );
    }
}
