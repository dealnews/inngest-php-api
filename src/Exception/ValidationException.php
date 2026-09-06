<?php

namespace DealNews\InngestApi\Exception;

/**
 * Thrown for HTTP 400/409/422 responses: the request was rejected because
 * of invalid input or a conflicting/unprocessable state.
 */
class ValidationException extends ApiException {

}
