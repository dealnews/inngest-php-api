<?php

namespace DealNews\InngestApi\Model\Sessions;

/**
 * The lifecycle status of a function run within a session.
 */
enum FunctionRunStatus: string {
    case Unspecified = 'UNSPECIFIED';
    case Queued      = 'QUEUED';
    case Running     = 'RUNNING';
    case Completed   = 'COMPLETED';
    case Failed      = 'FAILED';
    case Cancelled   = 'CANCELLED';
}
