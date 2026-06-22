<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Handler;

use ZJKiza\ResponseProcessor\Contract\ProcessorHandlerInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final readonly class AttributeHandler implements ProcessorHandlerInterface
{
    public function __construct(
        private ProcessorEngine $engine,
        private ResolverFactory $resolverFactory,
        private string          $attributeClass,
    ) {}

    public function process(mixed $data, ViewEvent $event): void
    {
        $this->engine->process(
            $data,
            $this->resolverFactory->forAttribute($this->attributeClass),
        );
    }
}
