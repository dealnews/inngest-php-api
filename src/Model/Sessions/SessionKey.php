<?php

namespace DealNews\InngestApi\Model\Sessions;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A session key observed in an environment. A session key groups together
 * the individual sessions run under it.
 */
class SessionKey {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?\DateTimeImmutable $created_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:         $data['id'] ?? null,
            created_at: DateTimeConverter::parse($data['createdAt'] ?? null),
        );
    }
}
