<?php

namespace DealNews\InngestApi\Model\Runs;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A single span in a function run's trace tree. Spans nest via
 * `children`, forming the full execution tree rooted at
 * `FunctionTrace::$root_span`.
 */
class TraceSpan {

    /**
     * @param array<string, mixed>|null $input
     * @param array<string, mixed>|null $output
     * @param TraceSpanMetadata[] $metadata
     * @param TraceSpan[] $children
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $step_id = null,
        public readonly ?TraceStepOp $step_op = null,
        public readonly ?TraceSpanStatus $status = null,
        public readonly ?string $duration_ms = null,
        public readonly ?array $input = null,
        public readonly ?array $output = null,
        public readonly ?\DateTimeImmutable $queued_at = null,
        public readonly ?\DateTimeImmutable $started_at = null,
        public readonly ?\DateTimeImmutable $ended_at = null,
        public readonly array $metadata = [],
        public readonly array $children = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:          $data['id']     ?? null,
            name:        $data['name']   ?? null,
            step_id:     $data['stepId'] ?? null,
            step_op:     isset($data['stepOp']) ? TraceStepOp::tryFrom($data['stepOp']) : null,
            status:      isset($data['status']) ? TraceSpanStatus::tryFrom($data['status']) : null,
            duration_ms: $data['durationMs'] ?? null,
            input:       $data['input']      ?? null,
            output:      $data['output']     ?? null,
            queued_at:   DateTimeConverter::parse($data['queuedAt']  ?? null),
            started_at:  DateTimeConverter::parse($data['startedAt'] ?? null),
            ended_at:    DateTimeConverter::parse($data['endedAt']   ?? null),
            metadata:    array_map(static fn (array $item) => TraceSpanMetadata::fromArray($item), $data['metadata'] ?? []),
            children:    array_map(static fn (array $item) => self::fromArray($item), $data['children'] ?? []),
        );
    }
}
