<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * A single score to submit for a run, or one of its steps. Serializes
 * back to the wire format expected by `POST /runs/{runId}/scores`, whose
 * body is a JSON array of these.
 */
class ScoreInput {

    public function __construct(
        public readonly ?string $name = null,
        public readonly mixed $value = null,
        public readonly ?string $step_id = null,
        public readonly ?ScoreExperiment $experiment = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            name:       $data['name']   ?? null,
            value:      $data['value']  ?? null,
            step_id:    $data['stepId'] ?? null,
            experiment: isset($data['experiment']) ? ScoreExperiment::fromArray($data['experiment']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return array_filter([
            'name'       => $this->name,
            'value'      => $this->value,
            'stepId'     => $this->step_id,
            'experiment' => $this->experiment?->toArray(),
        ], static fn ($value) => $value !== null);
    }
}
