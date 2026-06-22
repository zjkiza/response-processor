<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Handler;

use Symfony\Component\HttpKernel\Event\ViewEvent;
use ZJKiza\ResponseProcessor\Contract\ProcessorHandlerInterface;

final readonly class AttributeHandler implements ProcessorHandlerInterface
{
    public function __construct(
        private ProcessorEngine $engine,
        private ResolverFactory $resolverFactory,
        private string $attributeClass,
    ) {
    }

    public function process(mixed $data, ViewEvent $event): void
    {
        $this->engine->process(
            $data,
            $this->resolverFactory->forAttribute($this->attributeClass),
        );
    }
}
