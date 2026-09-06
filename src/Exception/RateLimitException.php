<?php

namespace DealNews\InngestApi\Exception;

/**
 * Thrown for HTTP 429 responses: the caller has been rate limited.
 */
class RateLimitException extends ApiException {

}
