<?php

namespace DealNews\InngestApi\Model\Apps;

/**
 * How the app's SDK endpoint is reached: a served HTTP endpoint, an
 * outbound connect (websocket) session, or the API method.
 */
enum AppMethod: string {
    case Unspecified = 'UNSPECIFIED';
    case Serve       = 'SERVE';
    case Connect     = 'CONNECT';
    case Api         = 'API';
}
