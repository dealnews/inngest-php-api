<?php

namespace DealNews\InngestApi\Pagination;

use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\ResponseMetadata;

/**
 * A page of results from a list endpoint, paired with cursor pagination
 * info and response metadata.
 */
class PaginatedResult {

    /**
     * @param object[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly ?Page $page = null,
        public readonly ?ResponseMetadata $metadata = null,
    ) {
    }
}
