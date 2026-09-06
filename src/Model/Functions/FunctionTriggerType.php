<?php

namespace DealNews\InngestApi\Model\Functions;

/**
 * The kind of trigger that starts a function run: an event or a cron
 * schedule.
 */
enum FunctionTriggerType: string {
    case Unspecified = 'UNSPECIFIED';
    case Event       = 'EVENT';
    case Cron        = 'CRON';
}
