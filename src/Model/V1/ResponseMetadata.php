<?php

namespace DealNews\InngestApi\Model\V1;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * The `metadata` envelope attached to v1 list/get responses.
 */
class ResponseMetadata {

    public function __construct(
        public readonly ?\DateTimeImmutable $fetched_at = null,
        public readonly ?\DateTimeImmutable $cached_until = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            fetched_at:   DateTimeConverter::parse($data['fetchedAt'] ?? null),
            cached_until: DateTimeConverter::parse($data['cachedUntil'] ?? null),
        );
    }
}
