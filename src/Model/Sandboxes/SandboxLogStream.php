<?php

namespace DealNews\InngestApi\Model\Sandboxes;

/**
 * Which standard stream a log chunk was written to.
 */
enum SandboxLogStream: string {
    case Unspecified  = 'UNSPECIFIED';
    case Stdout       = 'STDOUT';
    case Stderr       = 'STDERR';
}
