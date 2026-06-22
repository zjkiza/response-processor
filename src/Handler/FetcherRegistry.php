<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Handler;

use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;

final class FetcherRegistry
{
    /** @var array<class-string, DataFetcherInterface> */
    private array $fetchers = [];

    /**
     * @param iterable<DataFetcherInterface> $fetchers
     */
    public function __construct(iterable $fetchers)
    {
        foreach ($fetchers as $fetcher) {
            $this->fetchers[$fetcher::class] = $fetcher;
        }
    }

    public function get(string $class): DataFetcherInterface
    {
        if (!isset($this->fetchers[$class])) {
            throw new \RuntimeException(\sprintf('Data fetcher "%s" is not registered.', $class));
        }

        return $this->fetchers[$class];
    }
}
