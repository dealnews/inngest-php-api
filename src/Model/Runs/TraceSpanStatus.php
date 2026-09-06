<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * The lifecycle status of a single trace span.
 */
enum TraceSpanStatus: string {
    case Unknown   = 'UNKNOWN';
    case Running   = 'RUNNING';
    case Completed = 'COMPLETED';
    case Failed    = 'FAILED';
    case Waiting   = 'WAITING';
    case Cancelled = 'CANCELLED';
    case Skipped   = 'SKIPPED';
}
