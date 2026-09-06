<?php

namespace DealNews\InngestApi\Model\Experiments;

/**
 * Run counts and aggregated score metrics for one experiment variant.
 */
class ExperimentVariantMetrics {

    /**
     * @param ExperimentVariantMetric[] $metrics
     */
    public function __construct(
        public readonly ?string $variant_name = null,
        public readonly ?int $run_count = null,
        public readonly array $metrics = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            variant_name: $data['variantName'] ?? null,
            run_count:    $data['runCount']    ?? null,
            metrics:      array_map(
                static fn (array $metric) => ExperimentVariantMetric::fromArray($metric),
                $data['metrics'] ?? [],
            ),
        );
    }
}
