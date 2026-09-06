<?php

namespace DealNews\InngestApi\Exception;

use DealNews\InngestApi\Model\ErrorDetail;

/**
 * Thrown when the Inngest API responds with an HTTP error status.
 */
class ApiException extends InngestException {

    /**
     * @param ErrorDetail[]        $errors
     * @param array<string, mixed> $raw_body
     */
    public function __construct(
        string $message,
        protected int $status_code,
        protected array $errors = [],
        protected array $raw_body = [],
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int {
        return $this->status_code;
    }

    /**
     * @return ErrorDetail[]
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRawBody(): array {
        return $this->raw_body;
    }
}
