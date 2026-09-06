<?php

namespace DealNews\InngestApi\Model\Apps;

/**
 * The result of requesting a sync for an app. A non-null `error` means the
 * sync was persisted but failed; this can happen even when the API call
 * itself succeeded (see `AppsResource::sync()`).
 */
class SyncAppResult {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $app_id = null,
        public readonly ?SyncStatus $status = null,
        public readonly ?SyncError $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:      $data['id']    ?? null,
            app_id:  $data['appId'] ?? null,
            status:  isset($data['status']) ? SyncStatus::tryFrom($data['status']) : null,
            error:   isset($data['error']) ? SyncError::fromArray($data['error']) : null,
        );
    }
}
