<?php

namespace DealNews\InngestApi\Model\Apps;

/**
 * Status of an app sync. `success` and `duplicate` are successful terminal
 * states, `error` is a failed terminal state, and `pending` means the sync
 * is still in progress.
 */
enum SyncStatus: string {
    case Pending   = 'pending';
    case Success   = 'success';
    case Error     = 'error';
    case Duplicate = 'duplicate';
}
