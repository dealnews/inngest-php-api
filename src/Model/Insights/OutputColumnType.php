<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * The data type of a single column in an Insights query result.
 */
enum OutputColumnType: string {
    case Unspecified = 'VALUE_TYPE_UNSPECIFIED';
    case String      = 'STRING';
    case Number      = 'NUMBER';
    case Boolean     = 'BOOLEAN';
    case DateTime    = 'DATETIME';
    case Complex     = 'COMPLEX';
}
