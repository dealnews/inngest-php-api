<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\HttpClient;

/**
 * Base class for resource-specific API clients.
 */
abstract class AbstractResource {

    public function __construct(
        protected HttpClient $http,
    ) {
    }
}
