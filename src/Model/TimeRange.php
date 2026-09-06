<?php

namespace DealNews\InngestApi\Model;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A from/until time range, as returned in some response metadata.
 */
class TimeRange {

    public function __construct(
        public readonly ?\DateTimeImmutable $from = null,
        public readonly ?\DateTimeImmutable $until = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            from:  DateTimeConverter::parse($data['from']  ?? null),
            until: DateTimeConverter::parse($data['until'] ?? null),
        );
    }
}
