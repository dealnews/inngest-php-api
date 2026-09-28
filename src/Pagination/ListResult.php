<?php

namespace DealNews\InngestApi\Pagination;

use DealNews\InngestApi\Model\V1\ResponseMetadata;

/**
 * A list of items from a v1 API list endpoint, paired with response
 * metadata. Unlike PaginatedResult, v1 list endpoints don't return cursor
 * pagination info in the response body.
 */
class ListResult {

    /**
     * @param object[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly ?ResponseMetadata $metadata = null,
    ) {
    }
}
