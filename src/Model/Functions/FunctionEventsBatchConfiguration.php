<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * Event batching configuration for a function.
 */
class FunctionEventsBatchConfiguration {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?int $max_size = null,
        public readonly ?string $timeout = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            key:      $data['key']     ?? null,
            max_size: $data['maxSize'] ?? null,
            timeout:  $data['timeout'] ?? null,
        );
    }
}
