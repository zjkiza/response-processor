<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Controller;

use ZJKiza\ResponseProcessor\Attribute\Processor;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto\MediaDto;

class TestController
{
    /**
     * @return list<MediaDto>
     */
    #[Processor('tag_handler')]
    #[Processor('presenter_handler')]
    public function testAction(): array
    {
        return [
            new MediaDto('a', 'name-a'),
            new MediaDto('b', 'name-b'),
            new MediaDto('c', 'name-c'),
        ];
    }

    /**
     * @return list<MediaDto>
     */
    #[Processor('tag_handler')]
    public function singleAction(): array
    {
        return [
            new MediaDto('a', 'name-a'),
        ];
    }

    /**
     * @return list<MediaDto>
     */
    public function noAttributeAction(): array
    {
        return [
            new MediaDto('a', 'name-a'),
        ];
    }
}
