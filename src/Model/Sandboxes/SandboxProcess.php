<?php

namespace DealNews\InngestApi\Model\Sandboxes;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A process running (or that has run) inside a sandbox.
 */
class SandboxProcess {

    /**
     * @param string[] $command
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?int $pid = null,
        public readonly array $command = [],
        public readonly ?SandboxProcessState $state = null,
        public readonly ?\DateTimeImmutable $started_at = null,
        public readonly ?\DateTimeImmutable $ended_at = null,
        public readonly ?int $exit_code = null,
        public readonly ?int $termination_signal = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:                  $data['id']      ?? null,
            pid:                 $data['pid']     ?? null,
            command:             $data['command'] ?? [],
            state:               isset($data['state']) ? SandboxProcessState::tryFrom($data['state']) : null,
            started_at:          DateTimeConverter::parse($data['startedAt'] ?? null),
            ended_at:            DateTimeConverter::parse($data['endedAt'] ?? null),
            exit_code:           $data['exitCode']          ?? null,
            termination_signal:  $data['terminationSignal'] ?? null,
        );
    }
}
