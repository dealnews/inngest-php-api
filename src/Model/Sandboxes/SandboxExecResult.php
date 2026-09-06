<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * The result of executing a command in a sandbox. `stdout` and `stderr`
 * are decoded from the base64 wire encoding used for byte fields.
 */
class SandboxExecResult {

    public function __construct(
        public readonly ?int $exit_code = null,
        public readonly ?string $stdout = null,
        public readonly ?string $stderr = null,
        public readonly ?string $encoding = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            exit_code: isset($data['exitCode']) ? (int) $data['exitCode'] : null,
            stdout:    self::decodeBytes($data['stdout'] ?? null),
            stderr:    self::decodeBytes($data['stderr'] ?? null),
            encoding:  $data['encoding'] ?? null,
        );
    }

    protected static function decodeBytes(?string $value): ?string {
        $return = null;

        if ($value !== null) {
            $decoded = base64_decode($value, true);
            $return  = $decoded !== false ? $decoded : $value;
        }

        return $return;
    }
}
