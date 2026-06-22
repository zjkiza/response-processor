<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Service;

use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto\TagDto;

final class TagFetcher implements DataFetcherInterface
{
    private array $data = [
        'a' => ['id' => 'tag_a', 'name' => 'Tag for A'],
        'b' => ['id' => 'tag_b', 'name' => 'Tag for B'],
        'c' => ['id' => 'tag_c', 'name' => 'Tag for C'],
    ];

    public function fetch(array $identifiers): array
    {
        $result = [];
        foreach ($identifiers as $id) {
            if (isset($this->data[$id])) {
                $result[$id] = new TagDto($this->data[$id]['id'], $this->data[$id]['name']);
            }
        }

        return $result;
    }
}
