<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * The severity of a non-fatal diagnostic raised while executing an
 * Insights query.
 */
enum DiagnosticSeverity: string {
    case Unspecified = 'SEVERITY_UNSPECIFIED';
    case Error       = 'ERROR';
    case Warning     = 'WARNING';
    case Info        = 'INFO';
}
