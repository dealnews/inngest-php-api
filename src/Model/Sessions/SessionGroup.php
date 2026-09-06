<?php

namespace DealNews\InngestApi\Model\Sessions;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A single session (identified by its session ID) observed under a
 * session key, summarizing the function runs that occurred within it.
 */
class SessionGroup {

    /**
     * @param FunctionRef[] $functions
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly array $functions = [],
        public readonly ?int $run_count = null,
        public readonly ?int $failed_run_count = null,
        public readonly ?float $failure_rate = null,
        public readonly ?\DateTimeImmutable $last_active_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:               $data['id'] ?? null,
            functions:        array_map(
                static fn (array $function) => FunctionRef::fromArray($function),
                $data['functions'] ?? [],
            ),
            run_count:        $data['runCount']       ?? null,
            failed_run_count: $data['failedRunCount'] ?? null,
            failure_rate:     $data['failureRate']    ?? null,
            last_active_at:   DateTimeConverter::parse($data['lastActiveAt'] ?? null),
        );
    }
}
