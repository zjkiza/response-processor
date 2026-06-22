<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Contract;

interface DataFetcherInterface
{
    /**
     * Retrieves data based on a list of identifiers (one query).
     *
     * @param string[] $identifiers
     *
     * @return array<string, object|list<object>> map identifier => single object or list of objects
     */
    public function fetch(array $identifiers): array;
}
