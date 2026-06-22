<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final class JsonResponseListener
{
    public function onKernelView(ViewEvent $event): void
    {
        $event->setResponse(new JsonResponse($event->getControllerResult()));
    }
}
