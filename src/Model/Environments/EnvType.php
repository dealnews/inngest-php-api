<?php

namespace DealNews\InngestApi\Model\Environments;

/**
 * The kind of environment: production, test, or a branch environment.
 */
enum EnvType: string {
    case Production = 'PRODUCTION';
    case Test       = 'TEST';
    case Branch     = 'BRANCH';
}
