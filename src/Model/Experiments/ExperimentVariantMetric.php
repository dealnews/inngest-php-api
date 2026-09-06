<?php

namespace DealNews\InngestApi\Model\Experiments;

/**
 * A single aggregated metric (e.g. a named score) for an experiment
 * variant.
 */
class ExperimentVariantMetric {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?float $min = null,
        public readonly ?float $max = null,
        public readonly ?float $avg = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            key: $data['key'] ?? null,
            min: $data['min'] ?? null,
            max: $data['max'] ?? null,
            avg: $data['avg'] ?? null,
        );
    }
}
