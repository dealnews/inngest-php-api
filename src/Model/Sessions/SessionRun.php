<?php

namespace DealNews\InngestApi\Model\Sessions;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A single function run associated with a session.
 */
class SessionRun {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $event_name = null,
        public readonly ?FunctionRef $function = null,
        public readonly ?FunctionRunStatus $status = null,
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
            id:         $data['id']        ?? null,
            event_name: $data['eventName'] ?? null,
            function:   isset($data['function']) ? FunctionRef::fromArray($data['function']) : null,
            status:     isset($data['status']) ? FunctionRunStatus::tryFrom($data['status']) : null,
            queued_at:  DateTimeConverter::parse($data['queuedAt'] ?? null),
            started_at: DateTimeConverter::parse($data['startedAt'] ?? null),
            ended_at:   DateTimeConverter::parse($data['endedAt'] ?? null),
        );
    }
}
