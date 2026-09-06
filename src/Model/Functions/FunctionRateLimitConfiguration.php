<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * Rate limit configuration for a function's trigger events.
 */
class FunctionRateLimitConfiguration {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?int $limit = null,
        public readonly ?string $period = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            key:    $data['key']    ?? null,
            limit:  $data['limit']  ?? null,
            period: $data['period'] ?? null,
        );
    }
}
