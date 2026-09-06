<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * How a singleton function handles a new run while one is already active.
 */
enum FunctionSingletonMode: string {
    case Unspecified = 'UNSPECIFIED';
    case Skip        = 'SKIP';
    case Cancel      = 'CANCEL';
}
