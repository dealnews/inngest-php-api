<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * The full set of runtime configuration options applied to a function.
 */
class FunctionConfiguration {

    /**
     * @param FunctionCancellationConfiguration[] $cancellations
     * @param FunctionConcurrencyConfiguration[] $concurrency
     */
    public function __construct(
        public readonly array $cancellations = [],
        public readonly array $concurrency = [],
        public readonly ?FunctionDebounceConfiguration $debounce = null,
        public readonly ?FunctionEventsBatchConfiguration $events_batch = null,
        public readonly ?string $priority = null,
        public readonly ?FunctionRateLimitConfiguration $rate_limit = null,
        public readonly ?FunctionRetryConfiguration $retries = null,
        public readonly ?FunctionSingletonConfiguration $singleton = null,
        public readonly ?FunctionThrottleConfiguration $throttle = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            cancellations: array_map(
                static fn (array $cancellation) => FunctionCancellationConfiguration::fromArray($cancellation),
                $data['cancellations'] ?? [],
            ),
            concurrency: array_map(
                static fn (array $concurrency) => FunctionConcurrencyConfiguration::fromArray($concurrency),
                $data['concurrency'] ?? [],
            ),
            debounce:     isset($data['debounce']) ? FunctionDebounceConfiguration::fromArray($data['debounce']) : null,
            events_batch: isset($data['eventsBatch']) ? FunctionEventsBatchConfiguration::fromArray($data['eventsBatch']) : null,
            priority:     $data['priority'] ?? null,
            rate_limit:   isset($data['rateLimit']) ? FunctionRateLimitConfiguration::fromArray($data['rateLimit']) : null,
            retries:      isset($data['retries']) ? FunctionRetryConfiguration::fromArray($data['retries']) : null,
            singleton:    isset($data['singleton']) ? FunctionSingletonConfiguration::fromArray($data['singleton']) : null,
            throttle:     isset($data['throttle']) ? FunctionThrottleConfiguration::fromArray($data['throttle']) : null,
        );
    }
}
