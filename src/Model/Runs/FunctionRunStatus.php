<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * The lifecycle status of a function run.
 */
enum FunctionRunStatus: string {
    case Unspecified = 'UNSPECIFIED';
    case Queued      = 'QUEUED';
    case Running     = 'RUNNING';
    case Completed   = 'COMPLETED';
    case Failed      = 'FAILED';
    case Cancelled   = 'CANCELLED';
}
