<?php

namespace DealNews\InngestApi\Model\Functions;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * The result of invoking a function.
 */
class InvokeResult {

    public function __construct(
        public readonly ?string $run_id = null,
        public readonly ?\DateTimeImmutable $queued_at = null,
        public readonly ?\DateTimeImmutable $started_at = null,
        public readonly ?\DateTimeImmutable $completed_at = null,
        public readonly ?string $result = null,
        public readonly ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            run_id:       $data['runId'] ?? null,
            queued_at:    DateTimeConverter::parse($data['queuedAt']    ?? null),
            started_at:   DateTimeConverter::parse($data['startedAt']  ?? null),
            completed_at: DateTimeConverter::parse($data['completedAt'] ?? null),
            result:       $data['result'] ?? null,
            error:        $data['error']  ?? null,
        );
    }
}
