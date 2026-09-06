<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * Describes what triggered a function run: a single event, a batch of
 * events, or a cron schedule.
 */
class RunTrigger {

    /**
     * @param string[] $event_ids
     */
    public function __construct(
        public readonly ?string $event_name = null,
        public readonly array $event_ids = [],
        public readonly ?string $batch_id = null,
        public readonly bool $is_batch = false,
        public readonly ?string $cron_schedule = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            event_name:    $data['eventName']    ?? null,
            event_ids:     $data['eventIds']     ?? [],
            batch_id:      $data['batchId']      ?? null,
            is_batch:      $data['isBatch']      ?? false,
            cron_schedule: $data['cronSchedule'] ?? null,
        );
    }
}
