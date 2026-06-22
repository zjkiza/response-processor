<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Service;

use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto\PresenterDto;

final class PresenterFetcher implements DataFetcherInterface
{
    private array $data = [
        'a' => [
            ['id' => 'presenter_a', 'name' => 'Presenter for A'],
            ['id' => 'presenter_a1', 'name' => 'Presenter for A1'],
        ],
        'b' => [
            ['id' => 'presenter_b', 'name' => 'Presenter for B'],
            ['id' => 'presenter_bb', 'name' => 'Presenter for Bb'],
        ],
        'c' => [
            ['id' => 'presenter_c1', 'name' => 'Presenter for C1'],
            ['id' => 'presenter_c2', 'name' => 'Presenter for C2'],
            ['id' => 'presenter_c3', 'name' => 'Presenter for C3'],
        ],
    ];

    public function fetch(array $identifiers): array
    {
        $result = [];
        foreach ($identifiers as $id) {
            if (isset($this->data[$id])) {
                foreach ($this->data[$id] as $presenter) {
                    $result[$id][] = new PresenterDto($presenter['id'], $presenter['name']);
                }
            }
        }

        return $result;
    }
}
