<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ZJKiza\ResponseProcessor\Contract\PropertyResolverInterface;
use ZJKiza\ResponseProcessor\Extractor\ProcessorIdentifierExtractor;
use ZJKiza\ResponseProcessor\Handler\AttributePropertyResolver;
use ZJKiza\ResponseProcessor\Handler\FetcherRegistry;
use ZJKiza\ResponseProcessor\Handler\ProcessorEngine;
use ZJKiza\ResponseProcessor\Tests\Resources\Attribute\InjectPresenter;
use ZJKiza\ResponseProcessor\Tests\Resources\Attribute\InjectTag;
use ZJKiza\ResponseProcessor\Tests\Resources\Controller\Dto\MediaDto;
use ZJKiza\ResponseProcessor\Tests\Resources\Service\PresenterFetcher;
use ZJKiza\ResponseProcessor\Tests\Resources\Service\TagFetcher;

final class ProcessorEngineTest extends TestCase
{
    private ProcessorEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new ProcessorEngine(new ProcessorIdentifierExtractor());
    }

    public function testProcessInjectsTagData(): void
    {
        $resolver = $this->createResolver(InjectTag::class, new TagFetcher());

        $dto = new MediaDto('a', 'name-a');
        $this->engine->process([$dto], $resolver);

        $this->assertNotNull($dto->tag);
        $this->assertSame('tag_a', $dto->tag->id);
        $this->assertSame('Tag for A', $dto->tag->name);
    }

    public function testProcessInjectsPresenterData(): void
    {
        $resolver = $this->createResolver(
            InjectPresenter::class,
            new PresenterFetcher(),
        );

        $dto = new MediaDto('a', 'name-a');
        $this->engine->process([$dto], $resolver);

        $this->assertNotNull($dto->presenters);
        $this->assertCount(2, $dto->presenters);
        $this->assertSame('presenter_a', $dto->presenters[0]->id);
    }

    public function testProcessSkipsObjectsWithoutIdentifier(): void
    {
        $resolver = $this->createResolver(InjectTag::class, new TagFetcher());

        $dto = new \stdClass();
        $this->engine->process([$dto], $resolver);

        $this->assertTrue(true);
    }

    public function testProcessHandlesEmptyArray(): void
    {
        $resolver = $this->createResolver(InjectTag::class, new TagFetcher());

        $this->engine->process([], $resolver);

        $this->assertTrue(true);
    }

    public function testProcessWithMultipleObjectsBatchesFetch(): void
    {
        $fetcher = new TagFetcher();
        $resolver = $this->createResolver(InjectTag::class, $fetcher);

        $dtoA = new MediaDto('a', 'name-a');
        $dtoB = new MediaDto('b', 'name-b');
        $dtoC = new MediaDto('c', 'name-c');
        $dtoX = new MediaDto('x', 'name-x');

        $this->engine->process([$dtoA, $dtoB, $dtoC, $dtoX], $resolver);

        $this->assertNotNull($dtoA->tag);
        $this->assertNotNull($dtoB->tag);
        $this->assertNotNull($dtoC->tag);
        $this->assertNull($dtoX->tag);
    }

    public function testProcessWithSingleObjectNormalizesToArray(): void
    {
        $resolver = $this->createResolver(InjectTag::class, new TagFetcher());

        $dto = new MediaDto('a', 'name-a');
        $this->engine->process($dto, $resolver);

        $this->assertNotNull($dto->tag);
    }

    private function createResolver(string $attributeClass, $fetcher): PropertyResolverInterface
    {
        $registry = new FetcherRegistry([$fetcher]);
        return new AttributePropertyResolver($attributeClass, $registry);
    }
}
