<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class Processor
{
    public function __construct(
        public string $handler,
    ) {
    }
}
