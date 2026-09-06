<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * The retry policy configured for a function.
 */
class FunctionRetryConfiguration {

    public function __construct(
        public readonly ?int $value = null,
        public readonly bool $is_default = false,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            value:      $data['value']     ?? null,
            is_default: $data['isDefault'] ?? false,
        );
    }
}
