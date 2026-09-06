<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * A score recorded against a function run, or one of its steps.
 */
class Score {

    public function __construct(
        public readonly ?string $name = null,
        public readonly mixed $value = null,
        public readonly ?string $run_id = null,
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
            run_id:     $data['runId']  ?? null,
            step_id:    $data['stepId'] ?? null,
            experiment: isset($data['experiment']) ? ScoreExperiment::fromArray($data['experiment']) : null,
        );
    }
}
