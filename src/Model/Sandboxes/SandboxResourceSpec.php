<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * The compute resources allocated to a sandbox.
 */
class SandboxResourceSpec {

    public function __construct(
        public readonly ?int $vcpu = null,
        public readonly ?int $memory_mb = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            vcpu:      isset($data['vcpu']) ? (int) $data['vcpu'] : null,
            memory_mb: isset($data['memoryMb']) ? (int) $data['memoryMb'] : null,
        );
    }
}
