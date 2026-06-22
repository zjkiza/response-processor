<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Attribute;

use ZJKiza\ResponseProcessor\Contract\ProcessorAttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class InjectPresenter implements ProcessorAttributeInterface
{
    public function __construct(
        public string $repository,
    ) {
    }
}
