<?php

namespace DealNews\InngestApi\Model\V1\Runs;

/**
 * The status of a v1 function run.
 */
enum FunctionRunStatus: string {
    case Running   = 'Running';
    case Completed = 'Completed';
    case Failed    = 'Failed';
    case Cancelled = 'Cancelled';
}
