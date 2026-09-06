<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * The numeric limit applied by a concurrency configuration.
 */
class FunctionConcurrencyLimitConfiguration {

    public function __construct(
        public readonly ?int $value = null,
        public readonly bool $is_plan_limit = false,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            value:         $data['value']       ?? null,
            is_plan_limit: $data['isPlanLimit'] ?? false,
        );
    }
}
