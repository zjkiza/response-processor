<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Service;

use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto\TagDto;

final class TagFetcher implements DataFetcherInterface
{
    /** @var array<string, array{id: string, name: string}> */
    private array $data = [
        'a' => ['id' => 'tag_a', 'name' => 'Tag for A'],
        'b' => ['id' => 'tag_b', 'name' => 'Tag for B'],
        'c' => ['id' => 'tag_c', 'name' => 'Tag for C'],
    ];

    public function fetch(iterable $identifiers): array
    {
        $result = [];
        foreach ($identifiers as $id) {
            if (isset($this->data[$id])) {
                /** @var array{id: string, name: string} $item */
                $item = $this->data[$id];
                $result[$id] = new TagDto($item['id'], $item['name']);
            }
        }

        return $result;
    }
}
