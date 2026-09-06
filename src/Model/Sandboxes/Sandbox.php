<?php

namespace DealNews\InngestApi\Model\Sandboxes;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A sandbox: an isolated, ephemeral compute environment used to run
 * commands and processes.
 */
class Sandbox {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?SandboxStatus $status = null,
        public readonly ?SandboxResourceSpec $resources = null,
        public readonly ?string $image_ref = null,
        public readonly ?string $vpc_id = null,
        public readonly ?string $error = null,
        public readonly ?\DateTimeImmutable $created_at = null,
        public readonly ?\DateTimeImmutable $started_at = null,
        public readonly ?\DateTimeImmutable $ended_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:         $data['id']       ?? null,
            name:       $data['name']     ?? null,
            status:     isset($data['status']) ? SandboxStatus::tryFrom($data['status']) : null,
            resources:  isset($data['resources']) ? SandboxResourceSpec::fromArray($data['resources']) : null,
            image_ref:  $data['imageRef'] ?? null,
            vpc_id:     $data['vpcId']    ?? null,
            error:      $data['error']    ?? null,
            created_at: DateTimeConverter::parse($data['createdAt'] ?? null),
            started_at: DateTimeConverter::parse($data['startedAt'] ?? null),
            ended_at:   DateTimeConverter::parse($data['endedAt']   ?? null),
        );
    }
}
