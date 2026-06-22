<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Listener;

use ZJKiza\ResponseProcessor\Attribute\Processor;
use ZJKiza\ResponseProcessor\Contract\ProcessorHandlerInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final class ProcessorListener
{
    private const SEPARATOR = '::';

    private array $controllerCache = [];

    /** @var ProcessorHandlerInterface[] */
    private array $handlers = [];

    public function __construct(
        iterable $handlers,
    ) {
        foreach ($handlers as $key => $handler) {
            /** @var ProcessorHandlerInterface $handler */
            $this->handlers[$key] = $handler;
        }
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $controller = $request->attributes->get('_controller');

        $controllerKey = $this->normalizeController($controller);
        if ($controllerKey === null) {
            return;
        }

        $handlerClasses = $this->getHandlerClasses($controllerKey);
        if (empty($handlerClasses)) {
            return;
        }

        foreach ($handlerClasses as $handlerKey) {
            if (!isset($this->handlers[$handlerKey])) {
                throw new \RuntimeException(\sprintf('Handler with key "%s" is not registered in ProcessorListener.', $handlerKey));
            }

            $this->handlers[$handlerKey]->process($event->getControllerResult(), $event);
        }
    }

    private function normalizeController(mixed $controller): ?string
    {
        if (\is_string($controller) && \str_contains($controller, self::SEPARATOR)) {
            return $controller;
        }

        if (\is_array($controller) && \count($controller) === 2) {
            [$object, $method] = $controller;
            $class = \is_object($object) ? $object::class : $object;
            return $class . self::SEPARATOR . $method;
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getHandlerClasses(string $key): array
    {
        if (isset($this->controllerCache[$key])) {
            return $this->controllerCache[$key];
        }

        [$class, $method] = \explode(self::SEPARATOR, $key, 2);

        try {
            $reflection = new \ReflectionMethod($class, $method);
            $attributes = $reflection->getAttributes(Processor::class);
        } catch (\ReflectionException) {
            return $this->controllerCache[$key] = [];
        }

        $handlerClasses = [];

        foreach ($attributes as $attribute) {
            $processorAttribute = $attribute->newInstance();
            $handlerClasses[] = $processorAttribute->handler;
        }

        $this->controllerCache[$key] = $handlerClasses;

        return $handlerClasses;
    }
}
