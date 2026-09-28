<?php

namespace DealNews\InngestApi\Model\V1\Signals;

/**
 * The result of resuming a function run that was awaiting a signal.
 */
class ResumeSignalResult {

    public function __construct(
        public readonly ?string $run_id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            run_id: $data['run_id'] ?? null,
        );
    }
}
