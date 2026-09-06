<?php

namespace DealNews\InngestApi\Exception;

/**
 * Thrown when a request to the Inngest API fails at the transport level,
 * e.g. a connection failure, before any HTTP response is received.
 */
class TransportException extends InngestException {

}
