<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ZJKiza\ResponseProcessor\DependencyInjection\ZJKizaResponseProcessorExtension;

final class ZJKizaResponseProcessorExtensionTest extends TestCase
{
    public function testGetAlias(): void
    {
        $extension = new ZJKizaResponseProcessorExtension();

        $this->assertSame('zjkiza_response_processor', $extension->getAlias());
    }

    public function testLoadSetsDefaultPriority(): void
    {
        $container = new ContainerBuilder();
        $extension = new ZJKizaResponseProcessorExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasParameter('zjkiza_response_processor.listener.priority'));
        $this->assertSame(45, $container->getParameter('zjkiza_response_processor.listener.priority'));
    }

    public function testLoadWithCustomPriority(): void
    {
        $container = new ContainerBuilder();
        $extension = new ZJKizaResponseProcessorExtension();

        $extension->load([
            'zjkiza_response_processor' => [
                'listener' => ['priority' => 10],
            ],
        ], $container);

        $this->assertSame(10, $container->getParameter('zjkiza_response_processor.listener.priority'));
    }

    public function testLoadRegistersProcessorListenerService(): void
    {
        $container = new ContainerBuilder();
        $extension = new ZJKizaResponseProcessorExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition(
            'ZJKiza\ResponseProcessor\Listener\ProcessorListener',
        ));
    }
}
