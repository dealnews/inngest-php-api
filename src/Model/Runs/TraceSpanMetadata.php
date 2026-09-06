<?php

namespace DealNews\InngestApi\Model\Runs;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A single key/value metadata entry attached to a trace span (e.g. AI
 * gateway request/response details).
 */
class TraceSpanMetadata {

    /**
     * @param array<string, string> $values
     */
    public function __construct(
        public readonly ?string $kind = null,
        public readonly ?string $scope = null,
        public readonly ?\DateTimeImmutable $updated_at = null,
        public readonly array $values = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            kind:       $data['kind']  ?? null,
            scope:      $data['scope'] ?? null,
            updated_at: DateTimeConverter::parse($data['updatedAt'] ?? null),
            values:     $data['values'] ?? [],
        );
    }
}
