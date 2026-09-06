<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * A condition under which a running function is cancelled.
 */
class FunctionCancellationConfiguration {

    public function __construct(
        public readonly ?string $event = null,
        public readonly ?string $condition = null,
        public readonly ?string $timeout = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            event:     $data['event']     ?? null,
            condition: $data['condition'] ?? null,
            timeout:   $data['timeout']   ?? null,
        );
    }
}
