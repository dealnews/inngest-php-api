<?php

namespace DealNews\InngestApi\Model\Runs;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * The canonical summary of a single function run.
 */
class FunctionRun {

    /**
     * @param array<string, mixed>|null $output
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?AppRef $app = null,
        public readonly ?FunctionRef $function = null,
        public readonly ?FunctionRunStatus $status = null,
        public readonly ?RunTrigger $trigger = null,
        public readonly ?string $duration_ms = null,
        public readonly ?array $output = null,
        public readonly ?\DateTimeImmutable $queued_at = null,
        public readonly ?\DateTimeImmutable $started_at = null,
        public readonly ?\DateTimeImmutable $ended_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:          $data['id'] ?? null,
            app:         isset($data['app']) ? AppRef::fromArray($data['app']) : null,
            function:    isset($data['function']) ? FunctionRef::fromArray($data['function']) : null,
            status:      isset($data['status']) ? FunctionRunStatus::tryFrom($data['status']) : null,
            trigger:     isset($data['trigger']) ? RunTrigger::fromArray($data['trigger']) : null,
            duration_ms: $data['durationMs'] ?? null,
            output:      $data['output']     ?? null,
            queued_at:   DateTimeConverter::parse($data['queuedAt'] ?? null),
            started_at:  DateTimeConverter::parse($data['startedAt'] ?? null),
            ended_at:    DateTimeConverter::parse($data['endedAt'] ?? null),
        );
    }
}
