<?php

namespace DealNews\InngestApi\Model\PartnerAccounts;

use DealNews\InngestApi\Support\DateTimeConverter;

/**
 * A sub-account created through the Partner API, including its initial
 * API key. The key is only ever returned at creation time.
 */
class NewPartnerAccount {

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $api_key = null,
        public readonly ?\DateTimeImmutable $created_at = null,
        public readonly ?\DateTimeImmutable $updated_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:         $data['id']     ?? null,
            name:       $data['name']   ?? null,
            email:      $data['email']  ?? null,
            api_key:    $data['apiKey'] ?? null,
            created_at: DateTimeConverter::parse($data['createdAt'] ?? null),
            updated_at: DateTimeConverter::parse($data['updatedAt'] ?? null),
        );
    }
}
