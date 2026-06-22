<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto;

use ZJKiza\ResponseProcessor\Attribute\ProcessorIdentifier;
use ZJKiza\ResponseProcessor\Tests\Resources\Attribute\InjectPresenter;
use ZJKiza\ResponseProcessor\Tests\Resources\Attribute\InjectTag;
use ZJKiza\ResponseProcessor\Tests\Resources\Service\PresenterFetcher;
use ZJKiza\ResponseProcessor\Tests\Resources\Service\TagFetcher;

class MediaDto
{
    public function __construct(
        #[ProcessorIdentifier]
        public string $id,
        public string $name,
        #[InjectTag(TagFetcher::class)]
        public ?TagDto $tag = null,
        #[InjectPresenter(PresenterFetcher::class)]
        public ?array $presenters = null,
    ) {
        // noop.
    }
}
