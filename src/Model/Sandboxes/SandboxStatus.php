<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * The lifecycle status of a sandbox.
 */
enum SandboxStatus: string {
    case Unspecified  = 'UNSPECIFIED';
    case Pending      = 'PENDING';
    case Starting     = 'STARTING';
    case Running      = 'RUNNING';
    case Paused       = 'PAUSED';
    case Terminating  = 'TERMINATING';
    case Terminated   = 'TERMINATED';
    case Failed       = 'FAILED';
}
