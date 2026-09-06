<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * Singleton configuration for a function, ensuring only one run is active
 * at a time for a given key.
 */
class FunctionSingletonConfiguration {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?FunctionSingletonMode $mode = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            key:  $data['key'] ?? null,
            mode: isset($data['mode']) ? FunctionSingletonMode::tryFrom($data['mode']) : null,
        );
    }
}
