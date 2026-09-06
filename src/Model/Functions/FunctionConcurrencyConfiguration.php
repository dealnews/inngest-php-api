<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * A single concurrency limit configured for a function.
 */
class FunctionConcurrencyConfiguration {

    public function __construct(
        public readonly ?string $key = null,
        public readonly ?FunctionConcurrencyLimitConfiguration $limit = null,
        public readonly ?FunctionConcurrencyScope $scope = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            key:   $data['key'] ?? null,
            limit: isset($data['limit']) ? FunctionConcurrencyLimitConfiguration::fromArray($data['limit']) : null,
            scope: isset($data['scope']) ? FunctionConcurrencyScope::tryFrom($data['scope']) : null,
        );
    }
}
