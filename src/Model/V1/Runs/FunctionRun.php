<?php

namespace DealNews\InngestApi\Model\V1\Runs;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A function run, as returned by the v1 API.
 */
class FunctionRun {

    public function __construct(
        public readonly ?string $run_id = null,
        public readonly ?\DateTimeImmutable $run_started_at = null,
        public readonly ?\DateTimeImmutable $ended_at = null,
        public readonly ?FunctionRunStatus $status = null,
        public readonly mixed $output = null,
        public readonly ?string $function_id = null,
        public readonly ?int $function_version = null,
        public readonly ?string $environment_id = null,
        public readonly ?string $event_id = null,
        public readonly ?string $batch_id = null,
        public readonly ?string $original_run_id = null,
        public readonly ?string $cron = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            run_id:           $data['run_id']          ?? null,
            run_started_at:   DateTimeConverter::parse($data['run_started_at'] ?? null),
            ended_at:         DateTimeConverter::parse($data['ended_at'] ?? null),
            status:           isset($data['status']) ? FunctionRunStatus::tryFrom($data['status']) : null,
            output:           $data['output']            ?? null,
            function_id:      $data['function_id']       ?? null,
            function_version: $data['function_version']  ?? null,
            environment_id:   $data['environment_id']    ?? null,
            event_id:         $data['event_id']          ?? null,
            batch_id:         $data['batch_id']          ?? null,
            original_run_id:  $data['original_run_id']   ?? null,
            cron:             $data['cron']              ?? null,
        );
    }
}
