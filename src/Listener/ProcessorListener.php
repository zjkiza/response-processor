<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Listener;

use Symfony\Component\HttpKernel\Event\ViewEvent;
use ZJKiza\ResponseProcessor\Attribute\Processor;
use ZJKiza\ResponseProcessor\Contract\ProcessorHandlerInterface;

final class ProcessorListener
{
    private const SEPARATOR = '::';

    /** @var array<string, string[]> */
    private array $controllerCache = [];

    /** @var array<string, ProcessorHandlerInterface> */
    private array $handlers = [];

    /**
     * @param iterable<string, ProcessorHandlerInterface> $handlers
     */
    public function __construct(
        iterable $handlers,
    ) {
        foreach ($handlers as $key => $handler) {
            $this->handlers[$key] = $handler;
        }
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $controller = $request->attributes->get('_controller');

        $controllerKey = $this->normalizeController($controller);
        if (null === $controllerKey) {
            return;
        }

        $handlerClasses = $this->getHandlerClasses($controllerKey);

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

        if (\is_array($controller) && 2 === \count($controller)) {
            [$object, $method] = $controller;

            if (!\is_string($method)) {
                return null;
            }

            $class = $this->resolveClass($object);

            if (null === $class) {
                return null;
            }

            return $class.self::SEPARATOR.$method;
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

        $parts = \explode(self::SEPARATOR, $key, 2);

        if (2 !== \count($parts)) {
            return $this->controllerCache[$key] = [];
        }

        [$class, $method] = $parts;

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

    private function resolveClass(mixed $object): ?string
    {
        if (\is_object($object)) {
            return $object::class;
        }

        if (\is_string($object)) {
            return $object;
        }

        return null;
    }
}
