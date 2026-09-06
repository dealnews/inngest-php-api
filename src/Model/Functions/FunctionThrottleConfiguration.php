<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * Throttle configuration for a function's trigger events.
 */
class FunctionThrottleConfiguration {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?int $limit = null,
        public readonly ?int $burst = null,
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
            burst:  $data['burst']  ?? null,
            period: $data['period'] ?? null,
        );
    }
}
