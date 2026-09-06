<?php

namespace DealNews\InngestApi\Model;

/**
 * A single error entry from an Inngest API error response.
 */
class ErrorDetail {

    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $message = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            code:    $data['code']    ?? null,
            message: $data['message'] ?? null,
        );
    }
}
