<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * Debounce configuration for a function's trigger events.
 */
class FunctionDebounceConfiguration {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?string $period = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            key:    $data['key']    ?? null,
            period: $data['period'] ?? null,
        );
    }
}
