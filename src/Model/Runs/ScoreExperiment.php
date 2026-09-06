<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * Ties a score to an experiment variant, for aggregation in experiment
 * reports.
 */
class ScoreExperiment {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $variant = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:      $data['id']      ?? null,
            variant: $data['variant'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return array_filter([
            'id'      => $this->id,
            'variant' => $this->variant,
        ], static fn ($value) => $value !== null);
    }
}
