<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use ZJKiza\ResponseProcessor\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    public function testGetConfigTreeBuilderReturnsTreeBuilder(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }

    public function testDefaultConfigHasDefaultPriority(): void
    {
        $configuration = new Configuration();
        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        $processor = new Processor();
        $config = $processor->process($tree, []);

        $this->assertSame(45, $config['listener']['priority']);
    }

    public function testConfigWithCustomPriority(): void
    {
        $configuration = new Configuration();
        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        $processor = new Processor();
        $config = $processor->process($tree, [[
            'listener' => ['priority' => 10],
        ]]);

        $this->assertSame(10, $config['listener']['priority']);
    }
}
