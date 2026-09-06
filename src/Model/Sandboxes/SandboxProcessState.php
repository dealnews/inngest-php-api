<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * The lifecycle state of a process running inside a sandbox.
 */
enum SandboxProcessState: string {
    case Unspecified  = 'UNSPECIFIED';
    case Starting     = 'STARTING';
    case Running      = 'RUNNING';
    case Exited       = 'EXITED';
    case Killed       = 'KILLED';
    case Failed       = 'FAILED';
    case Lost         = 'LOST';
}
