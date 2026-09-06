<?php

namespace DealNews\InngestApi\Model;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * The `metadata` envelope attached to most v2 API responses. Some
 * endpoints (e.g. sandboxes) only ever populate `fetched_at`; the other
 * fields are simply left null for those responses.
 */
class ResponseMetadata {

    public function __construct(
        public readonly ?\DateTimeImmutable $fetched_at = null,
        public readonly ?\DateTimeImmutable $cached_until = null,
        public readonly ?TimeRange $time_range = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            fetched_at:   DateTimeConverter::parse($data['fetchedAt'] ?? null),
            cached_until: DateTimeConverter::parse($data['cachedUntil'] ?? null),
            time_range:   isset($data['timeRange']) ? TimeRange::fromArray($data['timeRange']) : null,
        );
    }
}
