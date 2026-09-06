<?php

namespace DealNews\InngestApi\Model\Apps;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * An app registered in an Inngest environment.
 */
class App {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $app_version = null,
        public readonly ?AppMethod $method = null,
        public readonly ?int $function_count = null,
        public readonly bool $is_archived = false,
        public readonly ?AppSync $latest_sync = null,
        public readonly ?\DateTimeImmutable $created_at = null,
        public readonly ?\DateTimeImmutable $archived_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:             $data['id']         ?? null,
            name:           $data['name']       ?? null,
            app_version:    $data['appVersion'] ?? null,
            method:         isset($data['method']) ? AppMethod::tryFrom($data['method']) : null,
            function_count: $data['functionCount'] ?? null,
            is_archived:    $data['isArchived']    ?? false,
            latest_sync:    isset($data['latestSync']) ? AppSync::fromArray($data['latestSync']) : null,
            created_at:     DateTimeConverter::parse($data['createdAt']   ?? null),
            archived_at:    DateTimeConverter::parse($data['archivedAt'] ?? null),
        );
    }
}
