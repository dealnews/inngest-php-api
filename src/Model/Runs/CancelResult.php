<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * The result of cancelling a function run.
 */
class CancelResult {

    public function __construct(
        public readonly ?string $run_id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            run_id: $data['runId'] ?? null,
        );
    }
}
