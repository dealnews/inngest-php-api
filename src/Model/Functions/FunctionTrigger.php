<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * A single trigger (event or cron) that starts a function run.
 */
class FunctionTrigger {

    public function __construct(
        public readonly ?FunctionTriggerType $type = null,
        public readonly ?string $value = null,
        public readonly ?string $if = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            type:  isset($data['type']) ? FunctionTriggerType::tryFrom($data['type']) : null,
            value: $data['value'] ?? null,
            if:    $data['if']    ?? null,
        );
    }
}
