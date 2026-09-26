<?php

namespace DealNews\InngestApi\Model\V1\Cancellations;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A bulk cancellation: stops function runs matching an optional
 * expression that started within a given time range.
 */
class Cancellation {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $environment_id = null,
        public readonly ?string $function_internal_id = null,
        public readonly ?string $function_id = null,
        public readonly ?\DateTimeImmutable $started_before = null,
        public readonly ?\DateTimeImmutable $started_after = null,
        public readonly ?string $if = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:                    $data['id']                    ?? null,
            environment_id:        $data['environment_id']        ?? null,
            function_internal_id:  $data['function_internal_id']  ?? null,
            function_id:           $data['function_id']           ?? null,
            started_before:        DateTimeConverter::parse($data['started_before'] ?? null),
            started_after:         DateTimeConverter::parse($data['started_after'] ?? null),
            if:                    $data['if']                    ?? null,
        );
    }
}
