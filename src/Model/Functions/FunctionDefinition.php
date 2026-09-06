<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * A function's configuration and status details within an app. Named
 * `FunctionDefinition` rather than `Function` since the latter is a
 * reserved word in PHP.
 */
class FunctionDefinition {

    /**
     * @param FunctionTrigger[] $triggers
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $slug = null,
        public readonly ?string $name = null,
        public readonly ?FunctionApp $app = null,
        public readonly bool $is_archived = false,
        public readonly bool $is_paused = false,
        public readonly array $triggers = [],
        public readonly ?FunctionConfiguration $configuration = null,
        public readonly ?FunctionFailureHandler $failure_handler = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            id:              $data['id']   ?? null,
            slug:            $data['slug'] ?? null,
            name:            $data['name'] ?? null,
            app:             isset($data['app']) ? FunctionApp::fromArray($data['app']) : null,
            is_archived:     $data['isArchived'] ?? false,
            is_paused:       $data['isPaused']   ?? false,
            triggers:        array_map(
                static fn (array $trigger) => FunctionTrigger::fromArray($trigger),
                $data['triggers'] ?? [],
            ),
            configuration:   isset($data['configuration']) ? FunctionConfiguration::fromArray($data['configuration']) : null,
            failure_handler: isset($data['failureHandler']) ? FunctionFailureHandler::fromArray($data['failureHandler']) : null,
        );
    }
}
