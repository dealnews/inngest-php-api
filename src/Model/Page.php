<?php

namespace DealNews\InngestApi\Model;

/**
 * Cursor pagination metadata attached to list responses.
 */
class Page {

    public function __construct(
        public readonly ?string $cursor = null,
        public readonly bool $has_more = false,
        public readonly ?int $limit = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            cursor:   $data['cursor']   ?? null,
            has_more: $data['hasMore']  ?? false,
            limit:    $data['limit']    ?? null,
        );
    }
}
