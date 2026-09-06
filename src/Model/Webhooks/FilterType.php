<?php

namespace DealNews\InngestApi\Model\Webhooks;

/**
 * Whether an event filter allows or denies the listed events.
 */
enum FilterType: string {
    case Allow = 'ALLOW';
    case Deny  = 'DENY';
}
