<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * A non-fatal diagnostic (e.g. a warning or informational note) raised
 * while validating or executing an Insights query.
 */
class Diagnostic {

    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $message = null,
        public readonly ?DiagnosticPosition $position = null,
        public readonly ?DiagnosticSeverity $severity = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            code:     $data['code']    ?? null,
            message:  $data['message'] ?? null,
            position: isset($data['position']) ? DiagnosticPosition::fromArray($data['position']) : null,
            severity: isset($data['severity']) ? DiagnosticSeverity::tryFrom($data['severity']) : null,
        );
    }
}
