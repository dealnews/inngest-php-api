<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * The full trace tree for a single function run.
 */
class FunctionTrace {

    public function __construct(
        public readonly ?string $run_id = null,
        public readonly ?TraceSpan $root_span = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            run_id:    $data['runId'] ?? null,
            root_span: isset($data['rootSpan']) ? TraceSpan::fromArray($data['rootSpan']) : null,
        );
    }
}
