<?php

namespace DealNews\InngestApi\Model\Runs;

/**
 * The kind of step operation a trace span represents.
 */
enum TraceStepOp: string {
    case Unspecified   = 'UNSPECIFIED';
    case Run           = 'RUN';
    case Sleep         = 'SLEEP';
    case WaitForEvent  = 'WAIT_FOR_EVENT';
    case Invoke        = 'INVOKE';
    case SendEvent     = 'SEND_EVENT';
    case AiGateway     = 'AI_GATEWAY';
    case WaitForSignal = 'WAIT_FOR_SIGNAL';
}
