<?php

namespace DealNews\InngestApi\Model\Apps;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * Details of the most recent sync for an app.
 */
class AppSync {

    public function __construct(
        public readonly ?string $app_version = null,
        public readonly ?string $error = null,
        public readonly ?string $framework = null,
        public readonly ?string $sdk_language = null,
        public readonly ?string $sdk_version = null,
        public readonly ?SyncStatus $status = null,
        public readonly ?\DateTimeImmutable $synced_at = null,
        public readonly ?string $url = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            app_version:  $data['appVersion']   ?? null,
            error:        $data['error']        ?? null,
            framework:    $data['framework']    ?? null,
            sdk_language: $data['sdkLanguage']  ?? null,
            sdk_version:  $data['sdkVersion']   ?? null,
            status:       isset($data['status']) ? SyncStatus::tryFrom($data['status']) : null,
            synced_at:    DateTimeConverter::parse($data['syncedAt'] ?? null),
            url:          $data['url'] ?? null,
        );
    }
}
