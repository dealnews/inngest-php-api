<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * The scope a concurrency limit is applied within.
 */
enum FunctionConcurrencyScope: string {
    case Unspecified  = 'UNSPECIFIED';
    case Account      = 'ACCOUNT';
    case Environment  = 'ENVIRONMENT';
    case Function     = 'FUNCTION';
}
