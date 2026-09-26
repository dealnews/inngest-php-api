<?php

namespace DealNews\InngestApi\Model\V1\Runs;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A single job returned when listing a function run's jobs, in order of
 * earliest to latest within the function's queue.
 */
class Job {

    public function __construct(
        public readonly ?\DateTimeImmutable $at = null,
        public readonly ?int $position = null,
        public readonly ?int $attempt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            at:       DateTimeConverter::parse($data['at'] ?? null),
            position: $data['position'] ?? null,
            attempt:  $data['attempt']  ?? null,
        );
    }
}
