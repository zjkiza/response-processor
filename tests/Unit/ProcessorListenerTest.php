<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ZJKiza\ResponseProcessor\Contract\ProcessorHandlerInterface;
use ZJKiza\ResponseProcessor\Listener\ProcessorListener;

final class ProcessorListenerTest extends TestCase
{
    public function testOnKernelViewCallsCorrectHandler(): void
    {
        $handler = $this->createMock(ProcessorHandlerInterface::class);
        $handler->expects($this->once())
            ->method('process');

        $listener = new ProcessorListener(['tag_handler' => $handler]);

        $event = $this->createViewEvent(
            \ZJKiza\ResponseProcessor\Tests\Resources\Controller\TestController::class . '::testAction',
        );

        $listener->onKernelView($event);
    }

    public function testOnKernelViewSkipsWhenNoProcessorAttribute(): void
    {
        $handler = $this->createMock(ProcessorHandlerInterface::class);
        $handler->expects($this->never())
            ->method('process');

        $listener = new ProcessorListener(['tag_handler' => $handler]);

        $event = $this->createViewEvent(
            \ZJKiza\ResponseProcessor\Tests\Resources\Controller\TestController::class . '::noAttributeAction',
        );

        $listener->onKernelView($event);
    }

    public function testOnKernelViewThrowsOnUnregisteredHandler(): void
    {
        $listener = new ProcessorListener([]);

        $event = $this->createViewEvent(
            \ZJKiza\ResponseProcessor\Tests\Resources\Controller\TestController::class . '::testAction',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler with key "tag_handler" is not registered');

        $listener->onKernelView($event);
    }

    public function testOnKernelViewCallsMultipleHandlers(): void
    {
        $tagHandler = $this->createMock(ProcessorHandlerInterface::class);
        $tagHandler->expects($this->once())
            ->method('process');

        $presenterHandler = $this->createMock(ProcessorHandlerInterface::class);
        $presenterHandler->expects($this->once())
            ->method('process');

        $listener = new ProcessorListener([
            'tag_handler' => $tagHandler,
            'presenter_handler' => $presenterHandler,
        ]);

        $event = $this->createViewEvent(
            \ZJKiza\ResponseProcessor\Tests\Resources\Controller\TestController::class . '::testAction',
        );

        $listener->onKernelView($event);
    }

    private function createViewEvent(string $controller): ViewEvent
    {
        $request = new Request();
        $request->attributes->set('_controller', $controller);

        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, []);
    }
}
