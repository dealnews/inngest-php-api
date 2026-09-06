<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Insights;

use DealNews\InngestApi\Model\Insights\Diagnostic;
use DealNews\InngestApi\Model\Insights\DiagnosticSeverity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DiagnosticTest extends TestCase {

    public function testFromArrayMapsNestedPosition(): void {
        $diagnostic = Diagnostic::fromArray([
            'code'     => 'syntax_error',
            'message'  => 'Unexpected token',
            'severity' => 'ERROR',
            'position' => [
                'context' => 'SELECT * FROM',
                'start'   => 0,
                'end'     => 6,
            ],
        ]);

        $this->assertSame('syntax_error', $diagnostic->code);
        $this->assertSame(DiagnosticSeverity::Error, $diagnostic->severity);
        $this->assertSame('SELECT * FROM', $diagnostic->position->context);
        $this->assertSame(0, $diagnostic->position->start);
        $this->assertSame(6, $diagnostic->position->end);
    }

    public function testFromArrayWithoutPositionLeavesItNull(): void {
        $diagnostic = Diagnostic::fromArray([
            'code'    => 'info_note',
            'message' => 'Just a note',
        ]);

        $this->assertNull($diagnostic->position);
        $this->assertNull($diagnostic->severity);
    }

    #[DataProvider('severityProvider')]
    public function testFromArrayMapsSeverity(string $raw, DiagnosticSeverity $expected): void {
        $diagnostic = Diagnostic::fromArray(['severity' => $raw]);

        $this->assertSame($expected, $diagnostic->severity);
    }

    /**
     * @return array<string, array{0: string, 1: DiagnosticSeverity}>
     */
    public static function severityProvider(): array {
        return [
            'unspecified' => ['SEVERITY_UNSPECIFIED', DiagnosticSeverity::Unspecified],
            'error'       => ['ERROR', DiagnosticSeverity::Error],
            'warning'     => ['WARNING', DiagnosticSeverity::Warning],
            'info'        => ['INFO', DiagnosticSeverity::Info],
        ];
    }
}
